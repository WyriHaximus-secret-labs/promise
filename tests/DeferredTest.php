<?php

namespace React\Promise;

use React\Promise\PromiseAdapter\CallbackPromiseAdapter;

class DeferredTest extends TestCase
{
    use PromiseTest\FullTestTrait;

    public function getPromiseTestAdapter(callable $canceller = null)
    {
        $d = new Deferred($canceller);

        return new CallbackPromiseAdapter([
            'promise' => [$d, 'promise'],
            'resolve' => [$d, 'resolve'],
            'reject'  => [$d, 'reject'],
            'settle'  => [$d, 'resolve'],
        ]);
    }

    /** @test */
    public function shouldRejectWithoutCreatingGarbageCyclesIfCancellerRejectsWithException()
    {
        if ($this->getTestResultObject()->getCollectCodeCoverageInformation() === true) {
            $this->markTestSkipped('This test has memory leaks when code coverage is collected');
        }

        gc_collect_cycles();
        $deferred = new Deferred(function ($resolve, $reject) {
            $reject(new \Exception('foo'));
        });
        $deferred->promise()->cancel();
        unset($deferred);

        $this->assertSame(0, gc_collect_cycles());
    }

    /** @test */
    public function shouldRejectWithoutCreatingGarbageCyclesIfParentCancellerRejectsWithException()
    {
        if ($this->getTestResultObject()->getCollectCodeCoverageInformation() === true) {
            $this->markTestSkipped('This test has memory leaks when code coverage is collected');
        }

        gc_collect_cycles();
        $deferred = new Deferred(function ($resolve, $reject) {
            $reject(new \Exception('foo'));
        });
        $deferred->promise()->then()->cancel();
        unset($deferred);

        $this->assertSame(0, gc_collect_cycles());
    }

    /** @test */
    public function shouldRejectWithoutCreatingGarbageCyclesIfCancellerHoldsReferenceAndExplicitlyRejectWithException()
    {
        if ($this->getTestResultObject()->getCollectCodeCoverageInformation() === true) {
            $this->markTestSkipped('This test has memory leaks when code coverage is collected');
        }

        gc_collect_cycles();
        $deferred = new Deferred(function () use (&$deferred) { });
        $deferred->reject(new \Exception('foo'));
        unset($deferred);

        $this->assertSame(0, gc_collect_cycles());
    }
}
