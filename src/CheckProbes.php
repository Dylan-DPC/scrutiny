<?php

namespace Scrutiny;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Scrutiny\Measurements\NoMeasurement;

class CheckProbes
{
    /**
     * @var ProbeManager
     */
    protected $probeManager;

    /**
     * @var Repository
     */
    protected $cacheStore;

    public function __construct(ProbeManager $probeManager)
    {
        $this->probeManager = $probeManager;
        $this->cacheStore = Cache::store('scrutiny-file');
    }

    /**
     * @return CheckProbesResult[]|CheckProbeHistory
     */
    public function handle()
    {
        $callback = function () {
            $checks = $this->runChecks();
            return $this->addToResultSet($checks);
        };

        // no caching if in debug mode
        if (config('app.debug')) {
            return $callback();
        }

        return $this->cacheStore->remember('users', 1, $callback);
    }

    /**
     * @return CheckProbesResult
     */
    protected function runChecks()
    {
        $result = $this->probeManager->probes()
            ->reduce(function (CheckProbesResult $carry, Probe $probe) {
                try {
                    $measurement = $probe->check();

                    if ($measurement === null || !($measurement instanceof Measurement)) {
                        $measurement = new NoMeasurement();
                    }

                    $carry->addPassed($probe, $measurement);
                } catch (ProbeSkippedException $e) {
                    $carry->addSkipped($probe, $e);
                } catch (\Exception $e) {
                    $carry->addFailed($probe, $e);
                }

                return $carry;
            }, new CheckProbesResult());

        return $result;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    protected function getResultSet()
    {
        if (!$this->cacheStore->has('resultSet')) {
            return new CheckProbeHistory();
        }

        return $this->cacheStore->get('resultSet');
    }

    protected function addToResultSet(CheckProbesResult $result)
    {
        $resultSet = $this->getResultSet()->prepend($result)->slice(0, 300);

        $this->cacheStore->forget('resultSet');

        $this->cacheStore->forever('resultSet', $resultSet);

        return $resultSet;
    }
}
