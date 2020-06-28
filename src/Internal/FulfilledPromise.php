<?php

namespace React\Promise\Internal;

use React\Promise\Promise;
use React\Promise\PromiseInterface;
use function React\Promise\enqueue;
use function React\Promise\fatalError;
use function React\Promise\resolve;
use function React\Promise\unhandledException;

/**
 * @internal
 */
final class FulfilledPromise implements PromiseInterface
{
    private $value;

    public function __construct($value = null)
    {
        if ($value instanceof PromiseInterface) {
            throw new \InvalidArgumentException('You cannot create React\Promise\FulfilledPromise with a promise. Use React\Promise\resolve($promiseOrValue) instead.');
        }

        $this->value = $value;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null): PromiseInterface
    {
        if (null === $onFulfilled) {
            return $this;
        }

        return new Promise(function (callable $resolve, callable $reject) use ($onFulfilled): void {
            enqueue(function () use ($resolve, $reject, $onFulfilled): void {
                try {
                    $resolve($onFulfilled($this->value));
                } catch (\Throwable $exception) {
                    $reject($exception);
                }
            });
        });
    }

    public function done(callable $onFulfilled = null, callable $onRejected = null): void
    {
        if (null === $onFulfilled) {
            return;
        }

        enqueue(function () use ($onFulfilled) {
            try {
                $result = $onFulfilled($this->value);
            } catch (\Throwable $exception) {
                unhandledException($exception);
            }

            if ($result instanceof PromiseInterface) {
                $result->done();
            }
        });
    }

    public function otherwise(callable $onRejected): PromiseInterface
    {
        return $this;
    }

    public function always(callable $onFulfilledOrRejected): PromiseInterface
    {
        return $this->then(function ($value) use ($onFulfilledOrRejected): PromiseInterface {
            return resolve($onFulfilledOrRejected())->then(function () use ($value) {
                return $value;
            });
        });
    }

    public function cancel(): void
    {
    }
}
