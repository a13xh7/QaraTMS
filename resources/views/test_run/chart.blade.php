<div class="progress">
    <div class="progress-bar bg-success" role="progressbar" style="width: {{$testRun->getChartData()['passed'][1]}}%" title="Passed">
        {{$testRun->getChartData()['passed'][0]}}
    </div>

    <div class="progress-bar bg-danger" role="progressbar" style="width: {{$testRun->getChartData()['failed'][1]}}%" title="Failed">
        {{$testRun->getChartData()['failed'][0]}}
    </div>

    <div class="progress-bar bg-warning" role="progressbar" style="width: {{$testRun->getChartData()['blocked'][1]}}%" title="Blocked">
        {{$testRun->getChartData()['blocked'][0]}}
    </div>

    <div class="progress-bar bg-secondary" role="progressbar" style="width: {{$testRun->getChartData()['not_tested'][1]}}%" title="Not Tested">
        {{$testRun->getChartData()['not_tested'][0]}}
    </div>
</div>
