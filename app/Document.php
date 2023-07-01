<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Document extends Model
{
    use HasRecursiveRelationships;

  public function delete() {
    $descendants = $this->descendants;

    foreach ($descendants as $descendant) {
      $descendant->delete();
    }
    return parent::delete();
  }

}
