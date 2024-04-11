<?php

namespace TCG\Voyager\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use TCG\Voyager\Database\DatabaseUpdater;
use TCG\Voyager\Database\Schema\Column;
use TCG\Voyager\Database\Schema\Identifier;
use TCG\Voyager\Database\Schema\SchemaManager;
use TCG\Voyager\Database\Schema\Table;
use TCG\Voyager\Database\Types\Type;
use TCG\Voyager\Events\TableAdded;
use TCG\Voyager\Events\TableDeleted;
use TCG\Voyager\Events\TableUpdated;
use TCG\Voyager\Facades\Voyager;

class VoyagerDatabaseController extends Controller
{
    public function index()
    {
        $this->authorize('browse_database');

        $dataTypes = Voyager::model('DataType')->select('id', 'name', 'slug')->get()->keyBy('name')->toArray();

        $tables = array_map(function ($table) use ($dataTypes) {
            $table = Str::replaceFirst(DB::getTablePrefix(), '', $table);

            $table = [
                'prefix'     => DB::getTablePrefix(),
                'name'       => $table,
                'slug'       => $dataTypes[$table]['slug'] ?? null,
                'dataTypeId' => $dataTypes[$table]['id'] ?? null,
            ];

            return (object) $table;
        }, SchemaManager::listTableNames());

        return Voyager::view('voyager::tools.database.index')->with(compact('dataTypes', 'tables'));
    }

    /**
     * Create database table.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        $this->authorize('browse_database');

        $db = $this->prepareDbManager('create');

        return Voyager::view('voyager::tools.database.edit-add', compact('db'));
    }

    /**
     * Store new database table.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('browse_database');

        // try {
            $conn = 'database.connections.'.config('database.default');
            Type::registerCustomPlatformTypes();

            $table = $request->table;
            if (!is_array($request->table)) {
                $table = json_decode($request->table, true);
            }
            $table['options']['collate'] = config($conn.'.collation', 'utf8mb4_unicode_ci');
            $table['options']['charset'] = config($conn.'.charset', 'utf8mb4');

			$found = false;
			foreach ($array['columns'] as $column) {
				if ($column['name'] === 'deleted_at') {
					$found = true;
					break;
				}
			}

			if ($found) {
				$params['--softdelete'] = true;
				// echo "'deleted_at' exists in the array.";
			} else {
				// echo "'deleted_at' does not exist in the array.";
			}


			
			// Apparently this is done by the next command equally
            $table = Table::make($table);
            // $table = SchemaManager::createTable($table);

            if (isset($request->create_model) && $request->create_model == 'on') {
                $modelNamespace = config('voyager.models.namespace', app()->getNamespace());
                $params = [
                    // 'name' => $modelNamespace.Str::studly(Str::singular($table->name)),
                    'name' => $modelNamespace.Str::studly(Str::singular($table->name)),
                ];

                // if (in_array('deleted_at', $request->input('field.*'))) {
                //     $params['--softdelete'] = true;
                // }

                if (isset($request->create_migration) && $request->create_migration == 'on') {
                    $params['--migration'] = true;
                }

                Artisan::call('voyager:make:model', $params);
            } elseif (isset($request->create_migration) && $request->create_migration == 'on') {
                Artisan::call('make:migration', [
                    'name'    => 'create_'.$table->name.'_table',
                    '--table' => $table->name,
                ]);
            }
			
			// $tableobject = (object) $table;
            event(new TableAdded($table));

            return redirect()
               ->route('voyager.database.index')
               ->with($this->alertSuccess(__('voyager::database.success_create_table', ['table' => $table->name])));
        // } catch (Exception $e) {
            // return back()->with($this->alertException($e))->withInput();
        // }
    }

    /**
     * Edit database table.
     *
     * @param string $table
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function edit($table)
    {
        $this->authorize('browse_database');

        if (!SchemaManager::tableExists($table)) {
            return redirect()
                ->route('voyager.database.index')
                ->with($this->alertError(__('voyager::database.edit_table_not_exist')));
        }

        $db = $this->prepareDbManager('update', $table);

        return Voyager::view('voyager::tools.database.edit-add', compact('db'));
    }

    /**
     * Update database table.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $this->authorize('browse_database');

        $table = json_decode($request->table, true);

        try {
            DatabaseUpdater::update($table);
            // TODO: synch BREAD with Table
            // $this->cleanOldAndCreateNew($request->original_name, $request->name);
            event(new TableUpdated($table));
        } catch (Exception $e) {
            return back()->with($this->alertException($e))->withInput();
        }

        return redirect()
               ->route('voyager.database.index')
               ->with($this->alertSuccess(__('voyager::database.success_create_table', ['table' => $table['name']])));
    }

    protected function prepareDbManager($action, $table = '')
    {
        $db = new \stdClass();

        // Need to get the types first to register custom types
		// seems to be fixed list in L11?
        // $db->types = Type::getPlatformTypes();
        
$db->types = [
    'Numbers' => [
        ['name' => 'smallint'],
        ['name' => 'integer'],
        ['name' => 'bigint'],
        ['name' => 'decimal'],
        ['name' => 'numeric'],
        ['name' => 'real'],
        ['name' => 'double precision'],
        ['name' => 'serial'],
        ['name' => 'bigserial'],
    ],
    'Strings' => [
        ['name' => 'char'],
        ['name' => 'varchar'],
        ['name' => 'text'],
    ],
    'Binary' => [
        ['name' => 'bytea'],
    ],
    'Boolean' => [
        ['name' => 'boolean'],
    ],
    'Dates and Times' => [
        ['name' => 'date'],
        ['name' => 'timestamp'],
        ['name' => 'timestamp with time zone'],
        ['name' => 'time'],
        ['name' => 'time with time zone'],
        ['name' => 'interval'],
    ],
    'Network Addresses' => [
        ['name' => 'cidr'],
        ['name' => 'inet'],
        ['name' => 'macaddr'],
    ],
    'JSON' => [
        ['name' => 'json'],
        ['name' => 'jsonb'],
    ],
    'UUID' => [
        ['name' => 'uuid'],
    ],
    'Arrays' => [
        ['name' => 'array'],
    ],
    'Geometry' => [
        ['name' => 'point'],
        ['name' => 'line'],
        ['name' => 'lseg'],
        ['name' => 'box'],
        ['name' => 'path'],
        ['name' => 'polygon'],
        ['name' => 'circle'],
    ],
    'Range' => [
        ['name' => 'int4range'],
        ['name' => 'int8range'],
        ['name' => 'numrange'],
        ['name' => 'tsrange'],
        ['name' => 'tstzrange'],
        ['name' => 'daterange'],
    ]
];



        if ($action == 'update') {
            $db->table = SchemaManager::listTableDetails($table);
            $db->formAction = route('voyager.database.update', $table);
        } else {
            $db->table = new Table('New Table');

            // Add prefilled columns
            $db->table->addColumn('id', 'integer', [
                'unsigned'      => true,
                'notnull'       => true,
                'autoincrement' => true,
            ]);

            $db->table->setPrimaryKey(['id'], 'primary');

            $db->formAction = route('voyager.database.store');
        }

        $oldTable = old('table');
        $db->oldTable = $oldTable ? $oldTable : json_encode(null);
        $db->action = $action;
        $db->identifierRegex = Identifier::REGEX;
        $db->platform = SchemaManager::getDatabasePlatform()->getName();

        return $db;
    }

    public function cleanOldAndCreateNew($originalName, $tableName)
    {
        if (!empty($originalName) && $originalName != $tableName) {
            $dt = DB::table('data_types')->where('name', $originalName);
            if ($dt->get()) {
                $dt->delete();
            }

            $perm = DB::table('permissions')->where('table_name', $originalName);
            if ($perm->get()) {
                $perm->delete();
            }

            $params = ['name' => Str::studly(Str::singular($tableName))];
            Artisan::call('voyager:make:model', $params);
        }
    }

    public function reorder_column(Request $request)
    {
        $this->authorize('browse_database');

        if ($request->ajax()) {
            $table = $request->table;
            $column = $request->column;
            $after = $request->after;
            if ($after == null) {
                // SET COLUMN TO THE TOP
                DB::query("ALTER $table MyTable CHANGE COLUMN $column FIRST");
            }

            return 1;
        }

        return 0;
    }

    /**
     * Show table.
     *
     * @param string $table
     *
     * @return JSON
     */
    public function show($table)
    {
        $this->authorize('browse_database');

        $additional_attributes = [];
        $model_name = Voyager::model('DataType')->where('name', $table)->pluck('model_name')->first();
        if (isset($model_name)) {
            $model = app($model_name);
            if (isset($model->additional_attributes)) {
                foreach ($model->additional_attributes as $attribute) {
                    $additional_attributes[$attribute] = [];
                }
            }
        }

        return response()->json(collect(SchemaManager::describeTable($table))->merge($additional_attributes));
    }

    /**
     * Destroy table.
     *
     * @param string $table
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($table)
    {
        $this->authorize('browse_database');

        try {
            SchemaManager::dropTable($table);
            event(new TableDeleted($table));

            return redirect()
                ->route('voyager.database.index')
                ->with($this->alertSuccess(__('voyager::database.success_delete_table', ['table' => $table])));
        } catch (Exception $e) {
            return back()->with($this->alertException($e));
        }
    }
}
