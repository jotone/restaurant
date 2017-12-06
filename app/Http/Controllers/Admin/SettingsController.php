<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\CategoryTypes;
use App\Settings;

use App\Http\Controllers\AppController;
use Illuminate\Http\Request;

class SettingsController extends AppController
{
	/**
	 * GET|HEAD /admin/settings
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function index(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true) {
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($current_page, 'category_types');

			//Get pages settings
			$settings = Settings::where('type','=','settings')->orderBy('position','asc')->get();
			//Create options list
			$settings_list = [];
			foreach($settings as $setting){
				$settings_list[] = [
					'id'		=> $setting->id,
					'title'		=> $setting->title,
					'slug'		=> $setting->slug,
					'options'	=> json_decode($setting->options)
				];
			}

			$category_types = CategoryTypes::select('id','title')->where('enabled','=',1)->orderBy('title','asc')->get();
			$category_types = (!empty($category_types->all()))
				? $category_types->toArray()
				: null;

			return view('admin.settings', [
				'start'		=> $start,
				'page'		=> $current_page,
				'breadcrumbs'=> $breadcrumbs,
				'title'		=> $page->title,
				'content'	=> $settings_list,
				'categories'=> $category_types,
			]);
		}
	}


	/**
	 * PUT /admin/settings
	 * @param null $id
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function update($id = null, Request $request){
		$data = $request->all();
		$is_ajax = (isset($data['ajax']))? true: false;
		unset($data['_token']);
		unset($data['_method']);
		unset($data['id']);
		unset($data['save']);
		unset($data['ajax']);

		//Get settings ids
		$setting_ids = [];
		foreach($data as $key => $val){
			//Key is array like [name=>'',id=>'']
			$key = json_decode($key);
			$setting_ids[] = $key->id;
		}
		//Get unique settings ids
		$setting_ids = array_values(array_unique($setting_ids));

		//Create real DB options view
		$result_array = [];
		foreach($setting_ids as $id){
			$result = Settings::select('options')->find($id);
			//Get options for current settings
			$result_array[$id] = json_decode($result->options);

			//Fill result array
			foreach($result_array[$id] as $key => $value){
				$data_key = json_encode(['name'=>$key, 'id'=>$id]);
				if(isset($data[$data_key])){
					//Switch key type
					switch($key){
						case 'category_type':
							$result_array[$id]->$key = $data[$data_key];
						break;
						case 'default_characteristics':
							$result_array[$id]->$key = (!empty($data[$data_key]))? $data[$data_key]: '""';
						break;
						default:
							if($is_ajax){
								$result_array[$id]->$key = $data[$data_key];
							}else{
								$result_array[$id]->$key = ($data[$data_key] == 'on')? 1: 0;
							}
					}
				}else{
					$result_array[$id]->$key = ($key == 'default_characteristics')? '': 0;
				}
			}
		}

		//Save settings options
		foreach($result_array as $id => $options){
			$result = Settings::find($id);
			$result->options = json_encode($options);
			$result->save();
		}

		return ($is_ajax)
			? json_encode(['message'=>'success'])
			: redirect()->route('admin.settings.index');
	}
}