<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\CategoryTypes;
use App\Settings;

use App\Http\Controllers\AppController;
use Hamcrest\Core\Set;
use Illuminate\Http\Request;
class MainInfoController extends AppController
{
	/**
	 * GET|HEAD /admin/settings/main_info
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
			$settings = Settings::where('type','=','main_info')->orderBy('position','asc')->get();
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

			return view('admin.main_info', [
				'start'		=> $start,
				'page'		=> $current_page,
				'breadcrumbs'=> $breadcrumbs,
				'title'		=> $page->title,
				'content'	=> $settings_list
			]);
		}
	}


	/**
	 * PUT /admin/settings/main_info
	 * @param null $id
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function update($id = null, Request $request){
		$data = $request->all();

		$is_ajax = (isset($data['ajax']))? true: false;

		unset($data['_token']);
		unset($data['_method']);
		unset($data['save']);
		unset($data['ajax']);
		unset($data['_url']);

		$result_array = [];
		foreach($data as $key => $val){
			$key = json_decode($key);
			$val = array_diff($val,[null]);
			switch($key->name){
				case 'coordinates':
					$result_array[$key->id][$key->axis] = (!empty($val))? $val: '';
				break;
				default:
					$result_array[$key->id] = (!empty($val))? $val: '';
			}
		}

		foreach($result_array as $id => $val){
			$result = Settings::find($id);
			$result->options = json_encode($val);
			$result->save();
		}

		return ($is_ajax)
			? json_encode(['message'=>'success'])
			: redirect()->route('admin.main_info.index');
	}
}