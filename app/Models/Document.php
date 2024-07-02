<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

/**
 * @mixin IdeHelperDocument
 */
class Document extends Model
{
    use HasRecursiveRelationships;

    public function delete()
    {
        $descendants = $this->descendants;

        foreach ($descendants as $descendant) {
            $descendant->delete();
        }
        return parent::delete();
    }

}
