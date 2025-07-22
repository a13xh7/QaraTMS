<div class="progress">
    <div class="progress-bar bg-success" role="progressbar" style="width: {{$testRun->getChartData()['passed'][1]}}%"
         title="Passed">
        {{$testRun->getChartData()['passed'][0]}}
    </div>

    <div class="progress-bar bg-danger" role="progressbar" style="width: {{$testRun->getChartData()['failed'][1]}}%"
         title="Failed">
        {{$testRun->getChartData()['failed'][0]}}
    </div>

    <div class="progress-bar bg-warning" role="progressbar" style="width: {{$testRun->getChartData()['blocked'][1]}}%"
         title="Blocked">
        {{$testRun->getChartData()['blocked'][0]}}
    </div>

    <div class="progress-bar bg-secondary" role="progressbar"
         style="width: {{$testRun->getChartData()['todo'][1]}}%" title="To Do">
        {{$testRun->getChartData()['todo'][0]}}
    </div>

    <div class="progress-bar bg-info" role="progressbar"
         style="width: {{$testRun->getChartData()['skipped'][1]}}%" title="Skipped">
        {{$testRun->getChartData()['skipped'][0]}}
    </div>
</div>
