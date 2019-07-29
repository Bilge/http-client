<?php

namespace Amp\Http\Client\Connection\Internal;

use Amp\Struct;

/**
 * Used in Http2Connection.
 *
 * @internal
 */
final class Http2Stream
{
    use Struct;

    public const OPEN = 0;
    public const RESERVED = 0b0001;
    public const REMOTE_CLOSED = 0b0010;
    public const LOCAL_CLOSED = 0b0100;
    public const CLOSED = 0b0110;

    /** @var string|null Packed header string. */
    public $headers;

    /** @var int Max header length. */
    public $maxHeaderSize;

    /** @var int Max body length. */
    public $maxBodySize;

    /** @var int Bytes received on the stream. */
    public $received = 0;

    /** @var int */
    public $serverWindow;

    /** @var int */
    public $clientWindow;

    /** @var \Amp\Promise|null */
    public $pendingWrite;

    /** @var string */
    public $buffer = "";

    /** @var int */
    public $state;

    /** @var \Amp\Deferred|null */
    public $deferred;

    /** @var int Integer between 0 and 255 */
    public $priority = 0;

    /** @var int */
    public $dependency = 0;

    /** @var int|null */
    public $expectedLength;

    public function __construct(int $serverSize, int $clientSize, int $maxHeaderSize, int $maxBodySize, int $state = self::OPEN)
    {
        $this->serverWindow = $serverSize;
        $this->maxHeaderSize = $maxHeaderSize;
        $this->maxBodySize = $maxBodySize;
        $this->clientWindow = $clientSize;
        $this->state = $state;
    }
}
