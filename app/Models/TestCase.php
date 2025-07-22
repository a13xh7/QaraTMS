<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperTestCase
 */
class TestCase extends Model
{

    /* Data json format

     * {
          "preconditions": "45",

          "steps": [
            {
              "action": "step action",
              "result": "step result"
            },
            {
              "action": "step action",
              "result": "step result"
            }
          ]
        }
     */

    public function suite() {
        return $this->belongsTo(Suite::class, 'suite_id');
    }
}
