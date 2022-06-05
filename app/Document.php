<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use \Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;
}
