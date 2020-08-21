<?PHP

namespace Maruamyu\Core;

/**
 * ULID generation
 *
 * XXX count of CHARS is fixed 32 = 0x20. using bit operation
 *
 * required bcmath extention in 32bit PHP
 *
 * @see https://github.com/ulid/spec
 */
class Ulid
{
    const CHARS = [
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K',
        'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'V', 'W', 'X',
        'Y', 'Z',
    ];
    const RANDOM_BASE = 0x7FFFFFFF;  # 31 bit => Max 6 chars
    const CHARS_IN_ONCE_RANDOM = 5;  # for entropy...

    const TIME_PART_CHARS = 10;
    const RANDOM_PART_CHARS = 16;

    /** @var bool */
    public static $configForceBcmath = false;

    /** @var string[] 'timestamp' => 'time_part' */
    protected static $generatedTimePart = [];

    /** @var int[][] 'timestamp' => [random_part_char_indices] */
    protected static $generatedRandomPartCharIndices = [];

    /**
     * @param int|string|null $timestamp
     * @return string
     * @throws \RuntimeException not load "bcmath" extention
     */
    public static function generate($timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = static::getCurrentTimestamp();
        }
        $timestampString = strval($timestamp);
        return static::generateTimePart($timestampString) . static::generateRandomPart($timestampString);
    }

    /**
     * @return string millisec unixtime
     */
    public static function getCurrentTimestamp()
    {
        # "0.XXXxxxxxx unixtime"
        list($millisec, $unixtime) = explode(' ', microtime());
        return strval($unixtime) . substr($millisec . '00000', 2, 3);
    }

    /**
     * @param string $ulid
     * @return string millisec unixtime
     * @throws \RuntimeException invalid ulid or not load "bcmath" extention
     */
    public static function extractTimestamp($ulid)
    {
        $ulid = strtoupper(strval($ulid));

        $timePartCharIndices = [];
        for ($i = 0; $i < static::TIME_PART_CHARS; $i++) {
            $char = substr($ulid, $i, 1);
            $idx = array_search($char, static::CHARS, true);
            if ($idx === false) {
                throw new \RuntimeException('invalid ULID: ' . $ulid);
            }
            $timePartCharIndices[] = $idx;
        }

        if ((PHP_INT_SIZE >= 8) && !(static::$configForceBcmath)) {
            $timestamp = 0;
            # $charsCount = count(static::CHARS);
            # $workCharIndices = array_reverse($timePartCharIndices);  # Big Endian -> Little Endian
            # for ($i = 0; $i < count($workCharIndices); $i++) {
            #     $timestamp += $workCharIndices[$i] * pow($charsCount, $i);
            # }
            # Big Endian bit operation
            for ($i = 0; $i < count($timePartCharIndices); $i++) {
                $timestamp = ($timestamp << 5) | $timePartCharIndices[$i];
            }
            $timestamp = strval($timestamp);
        } elseif (extension_loaded('bcmath')) {
            $timestamp = '0';
            bcscale(0);
            $charsCount = strval(count(static::CHARS));
            $workCharIndices = array_reverse($timePartCharIndices);  # Big Endian -> Little Endian
            for ($i = 0; $i < count($workCharIndices); $i++) {
                $base = bcpow($charsCount, strval($i));
                $add = bcmul(strval($workCharIndices[$i]), $base);
                $timestamp = bcadd($timestamp, $add);
            }
        } else {
            throw new \RuntimeException('required bcmath extention in 32bit PHP');
        }
        return $timestamp;
    }

    /**
     * @param string $timestampString
     * @return string
     * @throws \RuntimeException not load "bcmath" extention
     */
    protected static function generateTimePart($timestampString)
    {
        if (isset(static::$generatedTimePart[$timestampString])) {
            $timePart = static::$generatedTimePart[$timestampString];
        } elseif ((PHP_INT_SIZE >= 8) && !(static::$configForceBcmath)) {
            $timePart = static::generateTimePart64bit($timestampString);
        } elseif (extension_loaded('bcmath')) {
            $timePart = static::generateTimePartBcmath($timestampString);
        } else {
            throw new \RuntimeException('required bcmath extention in 32bit PHP');
        }
        static::$generatedTimePart[$timestampString] = $timePart;
        return $timePart;
    }

    /**
     * @param string $timestampString
     * @return string
     */
    protected static function generateTimePart64bit($timestampString)
    {
        $timePart = '';
        $timestampInt = intval($timestampString);
        # $charsCount = count(static::CHARS);  # 32 = 0x20
        for ($i = 0; $i < static::TIME_PART_CHARS; $i++) {
            # XXX mod 32 => and 0x1F
            # $idx = $timestampInt % $charsCount;
            $idx = $timestampInt & 0x1F;
            $timePart = static::CHARS[$idx] . $timePart;
            # XXX div 32 => 5 bit right shift
            # $timestampInt = intval($timestampInt / $charsCount);
            $timestampInt = ($timestampInt >> 5);
        }
        return $timePart;
    }

    /**
     * @param string $timestampString
     * @return string
     */
    protected static function generateTimePartBcmath($timestampString)
    {
        $timePart = '';
        bcscale(0);
        $workTimestamp = $timestampString;
        $charsCount = strval(count(static::CHARS));
        for ($i = 0; $i < static::TIME_PART_CHARS; $i++) {
            $idx = intval(bcmod($workTimestamp, $charsCount));
            $timePart = static::CHARS[$idx] . $timePart;
            $workTimestamp = bcdiv($workTimestamp, $charsCount);
        }
        return $timePart;
    }

    /**
     * @param string $timestampString
     * @return string
     */
    protected static function generateRandomPart($timestampString)
    {
        $randomPart = '';
        $charsCount = count(static::CHARS);
        if (isset(static::$generatedRandomPartCharIndices[$timestampString])) {
            $randomPartCharIndices = static::$generatedRandomPartCharIndices[$timestampString];
            $incremental = 1;
            for ($i = (static::RANDOM_PART_CHARS - 1); $i >= 0; $i--) {
                $idx = $randomPartCharIndices[$i] + $incremental;
                if ($idx >= $charsCount) {
                    $idx -= $charsCount;
                } else {
                    $incremental = 0;
                }
                $randomPartCharIndices[$i] = $idx;
                $randomPart = static::CHARS[$idx] . $randomPart;
            }
        } else {
            $randomPart = '';
            $randomPartCharIndices = [];
            do {
                $random = Randomizer::randomInt(0, static::RANDOM_BASE);
                for ($i = 0; $i < static::CHARS_IN_ONCE_RANDOM; $i++) {
                    # XXX mod 32 => and 0x1F
                    # $idx = $random % $charsCount;
                    $idx = $random & 0x1F;
                    $randomPart .= static::CHARS[$idx];
                    $randomPartCharIndices[] = $idx;
                    # XXX div 32 => 5 bit right shift
                    # $random = intval($random / $charsCount);
                    $random = ($random >> 5);
                }
            } while (count($randomPartCharIndices) < static::RANDOM_PART_CHARS);

            $randomPart = substr($randomPart, 0, static::RANDOM_PART_CHARS);
            $randomPartCharIndices = array_slice($randomPartCharIndices, 0, static::RANDOM_PART_CHARS);
        }
        static::$generatedRandomPartCharIndices[$timestampString] = $randomPartCharIndices;
        return $randomPart;
    }
}
