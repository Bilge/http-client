<?php

namespace Amp\Http\Client\Internal;

use Amp\CancellationToken;
use function Amp\asyncCall;

/** @internal */
class CombinedCancellationToken implements CancellationToken
{
    private $tokens = [];

    private $nextId = "a";
    private $callbacks = [];
    private $exception;

    public function __construct(CancellationToken ...$tokens)
    {
        foreach ($tokens as $token) {
            $id = $token->subscribe(function ($exception) {
                $this->exception = $exception;

                $callbacks = $this->callbacks;
                $this->callbacks = [];

                foreach ($callbacks as $callback) {
                    asyncCall($callback, $this->exception);
                }
            });

            $this->tokens[] = [$token, $id];
        }
    }

    public function __destruct()
    {
        foreach ($this->tokens as [$token, $id]) {
            /** @var CancellationToken $token */
            $token->unsubscribe($id);
        }
    }

    /** @inheritdoc */
    public function subscribe(callable $callback): string
    {
        $id = $this->nextId++;

        if ($this->exception) {
            asyncCall($callback, $this->exception);
        } else {
            $this->callbacks[$id] = $callback;
        }

        return $id;
    }

    /** @inheritdoc */
    public function unsubscribe(string $id): void
    {
        unset($this->callbacks[$id]);
    }

    /** @inheritdoc */
    public function isRequested(): bool
    {
        foreach ($this->tokens as [$token]) {
            if ($token->isRequested()) {
                return true;
            }
        }

        return false;
    }

    /** @inheritdoc */
    public function throwIfRequested(): void
    {
        foreach ($this->tokens as [$token]) {
            $token->throwIfRequested();
        }
    }
}
