<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use CRUDbooster;
use CB;
use muhammadfahrul\crudbooster\controllers\CBController;
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

class AdminCmsUsersController extends CBController {


	public function cbInit() {
		# START CONFIGURATION DO NOT REMOVE THIS LINE
		$this->table               = 'cms_users';
		$this->primary_key         = 'id';
		$this->title_field         = "name";
		$this->button_action_style = 'button_icon';	
		$this->button_import 	   = FALSE;	
		$this->button_export 	   = FALSE;	
		# END CONFIGURATION DO NOT REMOVE THIS LINE
	
		# START COLUMNS DO NOT REMOVE THIS LINE
		$this->col = array();
		$this->col[] = array("label"=>"Name","name"=>"name");
		$this->col[] = array("label"=>"Email","name"=>"email");
		$this->col[] = array("label"=>"Privilege","name"=>"id_cms_privileges","join"=>"cms_privileges,name");
		$this->col[] = array("label"=>"Photo","name"=>"photo","image"=>1);		
		# END COLUMNS DO NOT REMOVE THIS LINE

		# START FORM DO NOT REMOVE THIS LINE
		$this->form = array(); 		
		$this->form[] = array("label"=>"Name","name"=>"name",'required'=>true,'validation'=>'required|alpha_spaces|min:3');
		$this->form[] = array("label"=>"Email","name"=>"email",'required'=>true,'type'=>'email','validation'=>'required|email|unique:cms_users,email,'.CRUDBooster::getCurrentId());		
		$this->form[] = array("label"=>"Photo","name"=>"photo","type"=>"upload","help"=>"Recommended resolution is 200x200px",'validation'=>'image|max:1000','resize_width'=>90,'resize_height'=>90);											
		$this->form[] = array("label"=>"Privilege","name"=>"id_cms_privileges","type"=>"select","datatable"=>"cms_privileges,name",'required'=>true);						
		// $this->form[] = array("label"=>"Password","name"=>"password","type"=>"password","help"=>"Please leave empty if not change");
		$this->form[] = array("label"=>"Password","name"=>"password","type"=>"password","help"=>"Please leave empty if not change");
		$this->form[] = array("label"=>"Password Confirmation","name"=>"password_confirmation","type"=>"password","help"=>"Please leave empty if not change");
		# END FORM DO NOT REMOVE THIS LINE
				
	}

	public function getProfile() {			

		$this->button_addmore = FALSE;
		$this->button_cancel  = FALSE;
		$this->button_show    = FALSE;			
		$this->button_add     = FALSE;
		$this->button_delete  = FALSE;	
		$this->hide_form 	  = ['id_cms_privileges'];

		$data['page_title'] = cbLang("label_button_profile");
		$data['row']        = CRUDBooster::first('cms_users',CRUDBooster::myId());

        return $this->view('crudbooster::default.form',$data);
	}
	public function hook_before_edit(&$postdata,$id) { 
		unset($postdata['password_confirmation']);
	}
	public function hook_before_add(&$postdata) {      
	    unset($postdata['password_confirmation']);
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
			$insertCmsUsers = DB::table('tb_user')->insert([
				'user_id' => $this->arr['id'],
				'privilege_id' => $this->arr['id_cms_privileges'],
				'name' => $this->arr['name'],
				'email' => $this->arr['email'],
				'avatar' => $this->arr['photo'],
				'password' => $this->arr['password'],
				'status' => 'ACTIVE',
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
			$dataTbUser = [
				'name' => $this->arr['name'],
				'avatar' => $this->arr['photo'],
				'email' => $this->arr['email'],
				'privilege_id' => $this->arr['id_cms_privileges'],
				'updated_at' => date('Y-m-d H:i:s')
			];
			if (!empty($this->arr['password'])) {
				$dataTbUser['password'] = $this->arr['password'];
			}
			$updateTbUser = DB::table('tb_user')->where('user_id', $id)->update($dataTbUser);
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
				if (CRUDBooster::isColumnExists('tb_user', 'deleted_at')) {
					$updateTbUser = DB::table('tb_user')->where('user_id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
				} else {
					$deleteTbUser = DB::table('tb_user')->where('user_id', $id)->delete();
				}
			}
        } else {
            $delete = DB::table($this->table)->where($this->primary_key, $id)->delete();
			if ($delete) {
				if (CRUDBooster::isColumnExists('tb_user', 'deleted_at')) {
					$updateTbUser = DB::table('tb_user')->where('user_id', $id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
				} else {
					$deleteTbUser = DB::table('tb_user')->where('user_id', $id)->delete();
				}
			}
        }

        $this->hook_after_delete($id);

        $url = g('return_url') ?: CRUDBooster::referer();

        CRUDBooster::redirect($url, cbLang("alert_delete_data_success"), 'success');
    }
}
