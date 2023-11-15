<?php namespace muhammadfahrul\crudbooster\controllers;

	use Session;
	use Request;
	use DB;
	use CRUDBooster;
	use CB;
	use muhammadfahrul\crudbooster\export\DefaultExportXls;
	use Illuminate\Support\Facades\App;
	use Illuminate\Support\Facades\Cache;
	use Illuminate\Support\Facades\Hash;
	use Illuminate\Support\Facades\PDF;
	use Illuminate\Support\Facades\Route;
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Facades\Validator;
	use Maatwebsite\Excel\Facades\Excel;
	use Schema;
	use muhammadfahrul\crudbooster\controllers\LogsController;
	use Illuminate\Validation\Rule;

	class AdminUserController extends \muhammadfahrul\crudbooster\controllers\CBController {

	    public function cbInit() {

			# START CONFIGURATION DO NOT REMOVE THIS LINE
			$this->title_field = "user_id";
			$this->limit = "20";
			$this->orderby = "created_at,desc";
			$this->global_privilege = false;
			$this->button_table_action = true;
			$this->button_bulk_action = true;
			$this->button_action_style = "button_icon";
			$this->button_add = true;
			$this->button_edit = true;
			$this->button_delete = true;
			$this->button_detail = true;
			$this->button_show = false;
			$this->button_filter = true;
			$this->button_import = false;
			$this->button_export = true;
			$this->table = "tb_users";
			# END CONFIGURATION DO NOT REMOVE THIS LINE

			# START COLUMNS DO NOT REMOVE THIS LINE
			$this->col = [];
			$this->col[] = ["label"=>"User ID","name"=>"user_id"];
			$this->col[] = ["label"=>"Privilege","name"=>"privilege_id","join"=>"cms_privileges,name","join_id"=>"id_cms_privileges"];
			$this->col[] = ["label"=>"Merchant","name"=>"merchant_id","join"=>"tb_merchant,name","join_id"=>"merchant_id","select_option"=>((new \App\Http\Controllers\AdminMerchantController())->hook_query_index(DB::table('tb_merchant'), true)->get())];
			$this->col[] = ["label"=>"Name","name"=>"name"];
			$this->col[] = ["label"=>"Email","name"=>"email"];
			$this->col[] = ["label"=>"Phone Number","name"=>"phone_number"];
			$this->col[] = ["label"=>"Avatar","name"=>"avatar","image"=>true];
			$this->col[] = ["label"=>"Is Active","name"=>"is_active",'callback_php' => '($row->is_active)?"<span class=\"label label-success\">Active</span>":"<span class=\"label label-default\">Not Active</span>"'];
			$this->col[] = ["label"=>"Created At","name"=>"created_at"];
			# END COLUMNS DO NOT REMOVE THIS LINE

			# START FORM DO NOT REMOVE THIS LINE
			$this->form = [];
			$this->form[] = ['label'=>'User ID','name'=>'user_id','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10','readonly'=>'true','value'=>time()];
			$this->form[] = ['label'=>'Privilege','name'=>'privilege_id','type'=>'select2','validation'=>'required|min:1|max:255','width'=>'col-sm-10','datatable'=>'cms_privileges,name','join_pk'=>'id_cms_privileges'];
			$this->form[] = ['label'=>'Merchant','name'=>'merchant_id','type'=>'select2','validation'=>'max:255','width'=>'col-sm-10','datatable'=>'tb_merchant,name','join_pk'=>'merchant_id'];
			$this->form[] = ['label'=>'Name','name'=>'name','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Email','name'=>'email','type'=>'email','validation'=>'required|min:1|max:255|email','width'=>'col-sm-10','placeholder'=>'Please enter a valid email address'];
			$this->form[] = ['label'=>'Phone Number','name'=>'phone_number','type'=>'number','validation'=>'required','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Password','name'=>'password','type'=>'password','validation'=>'min:1|max:32','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Avatar','name'=>'avatar','type'=>'upload','validation'=>'max:255','width'=>'col-sm-10'];
			$this->form[] = ['label'=>'Is Active','name'=>'is_active','type'=>'radio','validation'=>'required|integer','width'=>'col-sm-10','dataenum'=>'0|Not Active;1|Active'];
			$this->form[] = ['label'=>'Role','name'=>'role','type'=>'hidden','validation'=>'max:255','width'=>'col-sm-10'];
			if (\Request::segment(3) == 'detail') {
				$this->form[] = ['label'=>'Login Attempt','name'=>'login_attempt','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
				$this->form[] = ['label'=>'Last Login At','name'=>'last_login_at','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
				$this->form[] = ['label'=>'Last Login Ip','name'=>'last_login_ip','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
				$this->form[] = ['label'=>'Remember Token','name'=>'remember_token','type'=>'text','validation'=>'required|min:1|max:255','width'=>'col-sm-10'];
				$this->form[] = ['label'=>'Created At','name'=>'created_at','type'=>'datetime','validation'=>'required','width'=>'col-sm-10'];
				$this->form[] = ['label'=>'Updated At','name'=>'updated_at','type'=>'datetime','validation'=>'required','width'=>'col-sm-10'];
			}
			# END FORM DO NOT REMOVE THIS LINE

			# OLD START FORM
			//$this->form = [];
			//$this->form[] = ["label"=>"User Id","name"=>"user_id","type"=>"select2","required"=>TRUE,"validation"=>"required|min:1|max:255","datatable"=>"user,id"];
			//$this->form[] = ["label"=>"Merchant Id","name"=>"merchant_id","type"=>"select2","required"=>TRUE,"validation"=>"required|min:1|max:255","datatable"=>"merchant,id"];
			//$this->form[] = ["label"=>"Name","name"=>"name","type"=>"text","required"=>TRUE,"validation"=>"required|string|min:3|max:70","placeholder"=>"You can only enter the letter only"];
			//$this->form[] = ["label"=>"Email","name"=>"email","type"=>"email","required"=>TRUE,"validation"=>"required|min:1|max:255|email|unique:tb_users","placeholder"=>"Please enter a valid email address"];
			//$this->form[] = ["label"=>"Password","name"=>"password","type"=>"password","required"=>TRUE,"validation"=>"min:3|max:32","help"=>"Minimum 5 characters. Please leave empty if you did not change the password."];
			//$this->form[] = ["label"=>"Avatar","name"=>"avatar","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Is Active","name"=>"is_active","type"=>"radio","required"=>TRUE,"validation"=>"required|integer","dataenum"=>"Array"];
			//$this->form[] = ["label"=>"Login Attempt","name"=>"login_attempt","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Last Login Datetime","name"=>"last_login_at","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Last Login Ip","name"=>"last_login_ip","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Role","name"=>"role","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			//$this->form[] = ["label"=>"Remember Token","name"=>"remember_token","type"=>"text","required"=>TRUE,"validation"=>"required|min:1|max:255"];
			# OLD END FORM

			/* 
	        | ---------------------------------------------------------------------- 
	        | Sub Module
	        | ----------------------------------------------------------------------     
			| @label          = Label of action 
			| @path           = Path of sub module
			| @foreign_key 	  = foreign key of sub table/module
			| @button_color   = Bootstrap Class (primary,success,warning,danger)
			| @button_icon    = Font Awesome Class  
			| @parent_columns = Sparate with comma, e.g : name,created_at
	        | 
	        */
	        $this->sub_module = array();


	        /* 
	        | ---------------------------------------------------------------------- 
	        | Add More Action Button / Menu
	        | ----------------------------------------------------------------------     
	        | @label       = Label of action 
	        | @url         = Target URL, you can use field alias. e.g : [id], [name], [title], etc
	        | @icon        = Font awesome class icon. e.g : fa fa-bars
	        | @color 	   = Default is primary. (primary, warning, succecss, info)     
	        | @showIf 	   = If condition when action show. Use field alias. e.g : [id] == 1
	        | 
	        */
	        $this->addaction = array();


	        /* 
	        | ---------------------------------------------------------------------- 
	        | Add More Button Selected
	        | ----------------------------------------------------------------------     
	        | @label       = Label of action 
	        | @icon 	   = Icon from fontawesome
	        | @name 	   = Name of button 
	        | Then about the action, you should code at actionButtonSelected method 
	        | 
	        */
	        $this->button_selected = array();

	                
	        /* 
	        | ---------------------------------------------------------------------- 
	        | Add alert message to this module at overheader
	        | ----------------------------------------------------------------------     
	        | @message = Text of message 
	        | @type    = warning,success,danger,info        
	        | 
	        */
	        $this->alert        = array();
	                

	        
	        /* 
	        | ---------------------------------------------------------------------- 
	        | Add more button to header button 
	        | ----------------------------------------------------------------------     
	        | @label = Name of button 
	        | @url   = URL Target
	        | @icon  = Icon from Awesome.
	        | 
	        */
	        $this->index_button = array();



	        /* 
	        | ---------------------------------------------------------------------- 
	        | Customize Table Row Color
	        | ----------------------------------------------------------------------     
	        | @condition = If condition. You may use field alias. E.g : [id] == 1
	        | @color = Default is none. You can use bootstrap success,info,warning,danger,primary.        
	        | 
	        */
	        $this->table_row_color = array();     	          

	        
	        /*
	        | ---------------------------------------------------------------------- 
	        | You may use this bellow array to add statistic at dashboard 
	        | ---------------------------------------------------------------------- 
	        | @label, @count, @icon, @color 
	        |
	        */
	        $this->index_statistic = array();



	        /*
	        | ---------------------------------------------------------------------- 
	        | Add javascript at body 
	        | ---------------------------------------------------------------------- 
	        | javascript code in the variable 
	        | $this->script_js = "function() { ... }";
	        |
	        */
	        $this->script_js = NULL;


            /*
	        | ---------------------------------------------------------------------- 
	        | Include HTML Code before index table 
	        | ---------------------------------------------------------------------- 
	        | html code to display it before index table
	        | $this->pre_index_html = "<p>test</p>";
	        |
	        */
	        $this->pre_index_html = null;
	        
	        
	        
	        /*
	        | ---------------------------------------------------------------------- 
	        | Include HTML Code after index table 
	        | ---------------------------------------------------------------------- 
	        | html code to display it after index table
	        | $this->post_index_html = "<p>test</p>";
	        |
	        */
	        $this->post_index_html = null;
	        
	        
	        
	        /*
	        | ---------------------------------------------------------------------- 
	        | Include Javascript File 
	        | ---------------------------------------------------------------------- 
	        | URL of your javascript each array 
	        | $this->load_js[] = asset("myfile.js");
	        |
	        */
	        $this->load_js = array();
	        
	        
	        
	        /*
	        | ---------------------------------------------------------------------- 
	        | Add css style at body 
	        | ---------------------------------------------------------------------- 
	        | css code in the variable 
	        | $this->style_css = ".style{....}";
	        |
	        */
	        $this->style_css = NULL;
	        
	        
	        
	        /*
	        | ---------------------------------------------------------------------- 
	        | Include css File 
	        | ---------------------------------------------------------------------- 
	        | URL of your css each array 
	        | $this->load_css[] = asset("myfile.css");
	        |
	        */
	        $this->load_css = array();
	        
	        
	    }


	    /*
	    | ---------------------------------------------------------------------- 
	    | Hook for button selected
	    | ---------------------------------------------------------------------- 
	    | @id_selected = the id selected
	    | @button_name = the name of button
	    |
	    */
	    public function actionButtonSelected($id_selected,$button_name) {
	        //Your code here
	            
	    }


	    /*
	    | ---------------------------------------------------------------------- 
	    | Hook for manipulate query of index result 
	    | ---------------------------------------------------------------------- 
	    | @query = current sql query 
	    |
	    */
	    public function hook_query_index(&$query, $returnQuery=false) {
	        //Your code here
			$query->where('tb_users.user_id', '!=', '1');
			$query->where('tb_users.user_id', '!=', session()->get('user_id'));
	            
			if ($returnQuery) {
				return $query;
			}
	    }

	    /*
	    | ---------------------------------------------------------------------- 
	    | Hook for manipulate row of index table html 
	    | ---------------------------------------------------------------------- 
	    |
	    */    
	    public function hook_row_index($column_index,&$column_value) {	        
	    	//Your code here
	    }

	    /*
	    | ---------------------------------------------------------------------- 
	    | Hook for manipulate data input before add data is execute
	    | ---------------------------------------------------------------------- 
	    | @arr
	    |
	    */
	    public function hook_before_add(&$postdata) {        
	        //Your code here

	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for execute command after add public static function called 
	    | ---------------------------------------------------------------------- 
	    | @id = last insert id
	    | 
	    */
	    public function hook_after_add($id) {        
	        //Your code here

	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for manipulate data input before update data is execute
	    | ---------------------------------------------------------------------- 
	    | @postdata = input post data 
	    | @id       = current id 
	    | 
	    */
	    public function hook_before_edit(&$postdata,$id) {        
	        //Your code here

	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for execute command after edit public static function called
	    | ----------------------------------------------------------------------     
	    | @id       = current id 
	    | 
	    */
	    public function hook_after_edit($id) {
	        //Your code here 

	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for execute command before delete public static function called
	    | ----------------------------------------------------------------------     
	    | @id       = current id 
	    | 
	    */
	    public function hook_before_delete($id) {
	        //Your code here

	    }

	    /* 
	    | ---------------------------------------------------------------------- 
	    | Hook for execute command after delete public static function called
	    | ----------------------------------------------------------------------     
	    | @id       = current id 
	    | 
	    */
	    public function hook_after_delete($id) {
	        //Your code here

	    }



	    //By the way, you can still create your own method in here... :) 


		public function validation($id = null)
		{
	
			$request_all = Request::all();
			$array_input = [];
			foreach ($this->data_inputan as $di) {
				$ai = [];
				$name = $di['name'];
	
				if (! isset($request_all[$name])) {
					continue;
				}
	
				if ($di['type'] != 'upload') {
					if (@$di['required']) {
						$ai[] = 'required';
					}
				}
	
				if ($di['type'] == 'upload') {
					if ($id) {
						$row = DB::table($this->table)->where($this->primary_key, $id)->first();
						if ($row->{$di['name']} == '') {
							$ai[] = 'required';
						}
					}
				}
	
				if (@$di['min']) {
					$ai[] = 'min:'.$di['min'];
				}
				if (@$di['max']) {
					$ai[] = 'max:'.$di['max'];
				}
				if (@$di['image']) {
					$ai[] = 'image';
				}
				if (@$di['mimes']) {
					$ai[] = 'mimes:'.$di['mimes'];
				}
				$name = $di['name'];
				if (! $name) {
					continue;
				}
	
				if ($di['type'] == 'money') {
					$request_all[$name] = preg_replace('/[^\d-]+/', '', $request_all[$name]);
				}
	
				if ($di['type'] == 'child') {
					$slug_name = str_slug($di['label'], '');
					foreach ($di['columns'] as $child_col) {
						if (isset($child_col['validation'])) {
							//https://laracasts.com/discuss/channels/general-discussion/array-validation-is-not-working/
							if (strpos($child_col['validation'], 'required') !== false) {
								$array_input[$slug_name.'-'.$child_col['name']] = 'required';
	
								str_replace('required', '', $child_col['validation']);
							}
	
							$array_input[$slug_name.'-'.$child_col['name'].'.*'] = $child_col['validation'];
						}
					}
				}
	
				if (@$di['validation']) {
	
					$exp = explode('|', $di['validation']);
					if ($exp && count($exp)) {
						foreach ($exp as &$validationItem) {
							if (substr($validationItem, 0, 6) == 'unique') {
								$parseUnique = explode(',', str_replace('unique:', '', $validationItem));
								$uniqueTable = ($parseUnique[0]) ?: $this->table;
								$uniqueColumn = ($parseUnique[1]) ?: $name;
								$uniqueIgnoreId = ($parseUnique[2]) ?: (($id) ?: '');
	
								//Make sure table name
								$uniqueTable = CB::parseSqlTable($uniqueTable)['table'];
	
								//Rebuild unique rule
								$uniqueRebuild = [];
								$uniqueRebuild[] = $uniqueTable;
								$uniqueRebuild[] = $uniqueColumn;
								if ($uniqueIgnoreId) {
									$uniqueRebuild[] = $uniqueIgnoreId;
								} else {
									$uniqueRebuild[] = 'NULL';
								}
	
								//Check whether deleted_at exists or not
								if (CB::isColumnExists($uniqueTable, 'deleted_at')) {
									$uniqueRebuild[] = CB::findPrimaryKey($uniqueTable);
									$uniqueRebuild[] = 'deleted_at';
									$uniqueRebuild[] = 'NULL';
								}
								$uniqueRebuild = array_filter($uniqueRebuild);
								$validationItem = 'unique:'.implode(',', $uniqueRebuild);
							}
						}
					} else {
						$exp = [];
					}
	
					$validation = implode('|', $exp);
	
					$array_input[$name] = $validation;
				} else {
					$array_input[$name] = implode('|', $ai);
				}
			}
	
			$array_input['email'] = [
				'required',
				'min:1',
				'max:255',
				Rule::unique('tb_users', 'email')
			];
			$array_input['phone_number'] = [
				'required',
				'min:1',
				'max:255',
				Rule::unique('tb_users', 'phone_number')
			];

			if (!empty($id)) {
				$array_input['email'] = [
					'required',
					'min:1',
					'max:255',
					Rule::unique('tb_users', 'email')->ignore($id, 'user_id')
				];
				$array_input['phone_number'] = [
					'required',
					'min:1',
					'max:255',
					Rule::unique('tb_users', 'phone_number')->ignore($id, 'user_id')
				];
			}

			$validator = Validator::make($request_all, $array_input);
	
			if ($validator->fails()) {
				$message = $validator->messages();
				$message_all = $message->all();
	
				if (Request::ajax()) {
					$res = response()->json([
						'message' => cbLang('alert_validation_error', ['error' => implode(', ', $message_all)]),
						'message_type' => 'warning',
					])->send();
					exit;
				} else {
					$res = redirect()->back()->with("errors", $message)->with([
						'message' => cbLang('alert_validation_error', ['error' => implode(', ', $message_all)]),
						'message_type' => 'warning',
					])->withInput();
					\Session::driver()->save();
					$res->send();
					exit;
				}
			}
		}

		public function postAddSave()
		{
			$this->cbLoader();
			if (! CRUDBooster::isCreate() && $this->global_privilege == false) {
				CRUDBooster::insertLog(cbLang('log_try_add_save', [
					'name' => Request::input($this->title_field),
					'module' => CRUDBooster::getCurrentModule()->name,
				]));
				CRUDBooster::redirect(CRUDBooster::adminPath(), cbLang("denied_access"));
			}
	
			$this->validation();
			$this->input_assignment();
	
			if (Schema::hasColumn($this->table, 'created_at') && Schema::hasColumn($this->table, 'updated_at')) {
				$this->arr['created_at'] = date('Y-m-d H:i:s');
			}
	
			$this->hook_before_add($this->arr);
	
			$this->primary_key = CB::pk($this->table);
			if ($this->primary_key != 'id') {
				$this->arr[$this->primary_key] = (!empty($this->arr[$this->primary_key]) ? $this->arr[$this->primary_key] : time());
				$lastInsertId = $id = DB::table($this->table)->insert($this->arr);
				$id = $this->arr[$this->primary_key];
			} else {
				$lastInsertId = $id = DB::table($this->table)->insertGetId($this->arr);
			}
			if ($lastInsertId) {
				$insertCmsUsers = DB::table('cms_users')->insert([
					'id' => $this->arr['user_id'],
					'name' => $this->arr['name'],
					'photo' => $this->arr['avatar'],
					'email' => $this->arr['email'],
					'password' => $this->arr['password'],
					'id_cms_privileges' => $this->arr['privilege_id'],
					'status' => 'Active',
					'created_at' => date('Y-m-d H:i:s')
				]);
			}
			
			//fix bug if primary key is uuid
			if(isset($this->arr[$this->primary_key]) && $this->arr[$this->primary_key]!=$id) {
				$id = $this->arr[$this->primary_key];
			}
	
			//Looping Data Input Again After Insert
			foreach ($this->data_inputan as $ro) {
				$name = $ro['name'];
				if (! $name) {
					continue;
				}
	
				$inputdata = request($name);
	
				//Insert Data Checkbox if Type Datatable
				if ($ro['type'] == 'checkbox') {
					if ($ro['relationship_table']) {
						$datatable = explode(",", $ro['datatable'])[0];
						$foreignKey2 = CRUDBooster::getForeignKey($datatable, $ro['relationship_table']);
						$foreignKey = CRUDBooster::getForeignKey($this->table, $ro['relationship_table']);
						DB::table($ro['relationship_table'])->where($foreignKey, $id)->delete();
	
						if ($inputdata) {
							$relationship_table_pk = CB::pk($ro['relationship_table']);
							foreach ($inputdata as $input_id) {
								DB::table($ro['relationship_table'])->insert([
	//                                 $relationship_table_pk => CRUDBooster::newId($ro['relationship_table']),
									$foreignKey => $id,
									$foreignKey2 => $input_id,
								]);
							}
						}
					}
				}
	
				if ($ro['type'] == 'select2') {
					if ($ro['relationship_table']) {
						$datatable = explode(",", $ro['datatable'])[0];
						$foreignKey2 = CRUDBooster::getForeignKey($datatable, $ro['relationship_table']);
						$foreignKey = CRUDBooster::getForeignKey($this->table, $ro['relationship_table']);
						DB::table($ro['relationship_table'])->where($foreignKey, $id)->delete();
	
						if ($inputdata) {
							foreach ($inputdata as $input_id) {
								DB::table($ro['relationship_table'])->insert([
									$foreignKey => $id,
									$foreignKey2 => $input_id,
								]);
							}
						}
					}
				}
	
				if ($ro['type'] == 'child') {
					$name = str_slug($ro['label'], '');
					$columns = $ro['columns'];
					$getColName = request($name.'-'.$columns[0]['name']);
					$count_input_data = ($getColName)?(count($getColName) - 1):0;
					$child_array = [];
					$fk = $ro['foreign_key'];
	
					for ($i = 0; $i <= $count_input_data; $i++) {
						$column_data = [];
						foreach ($columns as $col) {
							$colname = $col['name'];
							$colvalue = request($name.'-'.$colname)[$i];
							if(isset($colvalue) === TRUE) {
								$column_data[$colname] = $colvalue;
							}
						}
						if(isset($column_data) === TRUE) {
							$column_data[$fk] = (!empty($id) ? $id : $lastInsertId);
							$child_array[] = $column_data;
						}
					}
	
					$childtable = CRUDBooster::parseSqlTable($ro['table'])['table'];
					DB::table($childtable)->insert($child_array);
				}
			}
	
			$this->hook_after_add($lastInsertId);
	
			$this->return_url = ($this->return_url) ? $this->return_url : request('return_url');
	
			//insert log
			CRUDBooster::insertLog(cbLang("log_add", ['name' => $this->arr[$this->title_field], 'module' => CRUDBooster::getCurrentModule()->name]));
	
			if ($this->return_url) {
				if (request('submit') == cbLang('button_save_more')) {
					CRUDBooster::redirect(Request::server('HTTP_REFERER'), cbLang("alert_add_data_success"), 'success');
				} else {
					CRUDBooster::redirect($this->return_url, cbLang("alert_add_data_success"), 'success');
				}
			} else {
				if (request('submit') == cbLang('button_save_more')) {
					CRUDBooster::redirect(CRUDBooster::mainpath('add'), cbLang("alert_add_data_success"), 'success');
				} else {
					CRUDBooster::redirect(CRUDBooster::mainpath(), cbLang("alert_add_data_success"), 'success');
				}
			}
		}

		public function postEditSave($id)
		{
			$this->cbLoader();
			$row = DB::table($this->table)->where($this->primary_key, $id)->first();
	
			if (! CRUDBooster::isUpdate() && $this->global_privilege == false) {
				CRUDBooster::insertLog(cbLang("log_try_add", ['name' => $row->{$this->title_field}, 'module' => CRUDBooster::getCurrentModule()->name]));
				CRUDBooster::redirect(CRUDBooster::adminPath(), cbLang('denied_access'));
			}
	
			$this->validation($id);
			$this->input_assignment($id);
	
			if (Schema::hasColumn($this->table, 'updated_at')) {
				$this->arr['updated_at'] = date('Y-m-d H:i:s');
			}
	
			$this->hook_before_edit($this->arr, $id);

			$update = DB::table($this->table)->where($this->primary_key, $id)->update($this->arr);
			if ($update) {
				$dataCmsUsers = [
					'name' => $this->arr['name'],
					'photo' => $this->arr['avatar'],
					'email' => $this->arr['email'],
					'id_cms_privileges' => $this->arr['privilege_id'],
					'updated_at' => date('Y-m-d H:i:s')
				];
				if (!empty($this->arr['password'])) {
					$dataCmsUsers['password'] = $this->arr['password'];
				}
				$updateCmsUsers = DB::table('cms_users')->where('id', $id)->update($dataCmsUsers);
			}
	
			//Looping Data Input Again After Insert
			foreach ($this->data_inputan as $ro) {
				$name = $ro['name'];
				if (! $name) {
					continue;
				}
	
				$inputdata = request($name);
	
				//Insert Data Checkbox if Type Datatable
				if ($ro['type'] == 'checkbox') {
					if ($ro['relationship_table']) {
						$datatable = explode(",", $ro['datatable'])[0];
	
						$foreignKey2 = CRUDBooster::getForeignKey($datatable, $ro['relationship_table']);
						$foreignKey = CRUDBooster::getForeignKey($this->table, $ro['relationship_table']);
						DB::table($ro['relationship_table'])->where($foreignKey, $id)->delete();
	
						if ($inputdata) {
							foreach ($inputdata as $input_id) {
								$relationship_table_pk = CB::pk($ro['relationship_table']);
								DB::table($ro['relationship_table'])->insert([
	//                                 $relationship_table_pk => CRUDBooster::newId($ro['relationship_table']),
									$foreignKey => $id,
									$foreignKey2 => $input_id,
								]);
							}
						}
					}
				}
	
				if ($ro['type'] == 'select2') {
					if ($ro['relationship_table'] && $ro["datatable_orig"] == "") {
						$datatable = explode(",", $ro['datatable'])[0];
	
						$foreignKey2 = CRUDBooster::getForeignKey($datatable, $ro['relationship_table']);
						$foreignKey = CRUDBooster::getForeignKey($this->table, $ro['relationship_table']);
						DB::table($ro['relationship_table'])->where($foreignKey, $id)->delete();
	
						if ($inputdata) {
							foreach ($inputdata as $input_id) {
								$relationship_table_pk = CB::pk($ro['relationship_table']);
								DB::table($ro['relationship_table'])->insert([
	//                                 $relationship_table_pk => CRUDBooster::newId($ro['relationship_table']),
									$foreignKey => $id,
									$foreignKey2 => $input_id,
								]);
							}
						}
					}
					if ($ro['relationship_table'] && $ro["datatable_orig"] != "") {
						$params = explode("|", $ro['datatable_orig']);
						if(!isset($params[2])) $params[2] = "id";
						DB::table($params[0])->where($params[2], $id)->update([$params[1] => implode(",",$inputdata)]);
					}
				}
	
				if ($ro['type'] == 'child') {
					$name = str_slug($ro['label'], '');
					$columns = $ro['columns'];
					$getColName = request($name.'-'.$columns[0]['name']);
					$count_input_data = ($getColName)?(count($getColName) - 1):0;
					$child_array = [];
					$childtable = CRUDBooster::parseSqlTable($ro['table'])['table'];
					$fk = $ro['foreign_key'];
	
					DB::table($childtable)->where($fk, $id)->delete();
					$lastId = CRUDBooster::newId($childtable);
					$childtablePK = CB::pk($childtable);
	
					for ($i = 0; $i <= $count_input_data; $i++) {
						$column_data = [];
						foreach ($columns as $col) {
							$colname = $col['name'];
							$colvalue = request($name.'-'.$colname)[$i];
							if(isset($colvalue) === TRUE) {
								$column_data[$colname] = $colvalue;
							}
						}
						if(isset($column_data) === TRUE){
							$column_data[$childtablePK] = $lastId;
							$column_data[$fk] = $id;
							$child_array[] = $column_data;
							$lastId++;
						}
					}
					$child_array = array_reverse($child_array);
					DB::table($childtable)->insert($child_array);
				}
			}
	
			$this->hook_after_edit($id);
	
			$this->return_url = ($this->return_url) ? $this->return_url : request('return_url');
	
			//insert log
			$old_values = json_decode(json_encode($row), true);
			CRUDBooster::insertLog(cbLang("log_update", [
				'name' => $this->arr[$this->title_field],
				'module' => CRUDBooster::getCurrentModule()->name,
			]), LogsController::displayDiff($old_values, $this->arr));
	
			if ($this->return_url) {
				CRUDBooster::redirect($this->return_url, cbLang("alert_update_data_success"), 'success');
			} else {
				if (request('submit') == cbLang('button_save_more')) {
					CRUDBooster::redirect(CRUDBooster::mainpath('add'), cbLang("alert_update_data_success"), 'success');
				} else {
					CRUDBooster::redirect(CRUDBooster::mainpath(), cbLang("alert_update_data_success"), 'success');
				}
			}
		}

		public function getDelete($id)
		{
			$this->cbLoader();
			$row = DB::table($this->table)->where($this->primary_key, $id)->first();
	
			if (! CRUDBooster::isDelete() && $this->global_privilege == false || $this->button_delete == false) {
				CRUDBooster::insertLog(cbLang("log_try_delete", [
					'name' => $row->{$this->title_field},
					'module' => CRUDBooster::getCurrentModule()->name,
				]));
				CRUDBooster::redirect(CRUDBooster::adminPath(), cbLang('denied_access'));
			}
	
			//insert log
			CRUDBooster::insertLog(cbLang("log_delete", ['name' => $row->{$this->title_field}, 'module' => CRUDBooster::getCurrentModule()->name]));
	
			$this->hook_before_delete($id);
	
			if (CRUDBooster::isColumnExists($this->table, 'deleted_at')) {
				$update = DB::table($this->table)->where($this->primary_key, $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
				if ($update) {
					if (CRUDBooster::isColumnExists('cms_users', 'deleted_at')) {
						$updateCmsUsers = DB::table('cms_users')->where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
					} else {
						$deleteCmsUsers = DB::table('cms_users')->where('id', $id)->delete();
					}
				}
			} else {
				$delete = DB::table($this->table)->where($this->primary_key, $id)->delete();
				if ($delete) {
					if (CRUDBooster::isColumnExists('cms_users', 'deleted_at')) {
						$updateCmsUsers = DB::table('cms_users')->where('id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
					} else {
						$deleteCmsUsers = DB::table('cms_users')->where('id', $id)->delete();
					}
				}
			}
	
			$this->hook_after_delete($id);
	
			$url = g('return_url') ?: CRUDBooster::referer();
	
			CRUDBooster::redirect($url, cbLang("alert_delete_data_success"), 'success');
		}
	}