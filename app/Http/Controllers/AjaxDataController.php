<?php

namespace App\Http\Controllers;

use App\Repository;
use App\TestPlan;
use App\Suite;
use App\Project;
use App\TestRun;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AjaxDataController extends Controller
{
    /*
     * RETURN [ { id: 1, parent_id: 0, title: "Branch 1", level: 1 }, {}, {} ],
     */
    public function getSuitesTree($id)
    {
//        Request $request
//        $repository_id = $request->repository_id;

        $repository = Repository::firstOrFail();
        $suitesTree = Suite::where('repository_id', $repository->id)->tree()->get()->toTree();

        $jsSuitesTree = [];

        foreach($suitesTree as $suite) {
            $this->recursiveGetData($suite, $jsSuitesTree);
        }

        //return json_encode($jsSuitesTree);
        return $jsSuitesTree;
    }

    private function recursiveGetData($suite, &$jsSuitesTree) {

        $jsSuitesTree[] = [
            'id' => $suite->id,
            'level' => $suite->depth + 1,
            'parent_id' => $suite->parent_id,
            'title' => $suite->title . '_id=_'.$suite->id
        ];

        foreach($suite->children as $suiteChild) {
            $this->recursiveGetData($suiteChild, $jsSuitesTree);
        }
    }




}
