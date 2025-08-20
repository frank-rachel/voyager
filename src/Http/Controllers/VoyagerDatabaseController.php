<?php

namespace TCG\Voyager\Http\Controllers;

use TCG\Voyager\Database\Schema\SchemaManager;
use TCG\Voyager\Facades\Voyager;

class VoyagerDatabaseController extends Controller
{
    /**
     * Return column information for the given table.
     */
    public function show(string $table)
    {
        $this->authorize('browse_database');

        $additionalAttributes = [];
        $modelName = Voyager::model('DataType')->where('name', $table)->pluck('model_name')->first();
        if ($modelName) {
            $model = app($modelName);
            if (isset($model->additional_attributes)) {
                foreach ($model->additional_attributes as $attribute) {
                    $additionalAttributes[$attribute] = [];
                }
            }
        }

        return response()->json(
            collect(SchemaManager::describeTable($table))->merge($additionalAttributes)
        );
    }
}
