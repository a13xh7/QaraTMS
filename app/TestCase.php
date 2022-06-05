<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
}
