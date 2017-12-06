<?php
namespace App\Console\Commands;

use App\AdminMenu;
use App\Roles;
use App\Settings;

use Illuminate\Console\Command;

class Install extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'admin:install';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle(){
		//Admin menu
		$data = [
			[//1
				'title'		=> 'Home',
				'slug'		=> '/admin',
				'img'		=> 'fa-home',
				'refer_to'	=> 0,
				'position'	=> 0
			],
			[//2
				'title'		=> 'Settings',
				'slug'		=> '/admin/settings',
				'img'		=> 'fa-wrench',
				'refer_to'	=> 0,
				'position'	=> 1
			],
			[//3
				'title'		=> 'Main Information',
				'slug'		=> '/admin/settings/main_info',
				'img'		=> 'fa-home',
				'refer_to'	=> 2,
				'position'	=> 0
			],
			[//4
				'title'		=> 'Gallery',
				'slug'		=> '/admin/settings/gallery',
				'img'		=> 'fa-picture-o',
				'refer_to'	=> 2,
				'position'	=> 1
			],
			[//5
				'title'		=> 'Pages',
				'slug'		=> '/admin/pages',
				'img'		=> 'fa-file-text-o',
				'refer_to'	=> 0,
				'position'	=> 3
			],
			[//6
				'title'		=> 'Templates',
				'slug'		=> '/admin/pages/templates',
				'img'		=> 'fa-file-o',
				'refer_to'	=> 5,
				'position'	=> 0
			],
			[//7
				'title'		=> 'Comments',
				'slug'		=> '/admin/comments',
				'img'		=> 'fa-comments-o',
				'refer_to'	=> 5,
				'position'	=> 1
			],
			[//8
				'title'		=> 'Categories',
				'slug'		=> '/admin/category_types',
				'img'		=> 'fa-list',
				'refer_to'	=> 0,
				'position'	=> 4
			],
			[//9
				'title'		=> 'News',
				'slug'		=> '/admin/news',
				'img'		=> 'fa-newspaper-o',
				'refer_to'	=> 0,
				'position'	=> 5
			],
			[//10
				'title'		=> 'Products',
				'slug'		=> '/admin/products',
				'img'		=> 'fa-shopping-cart',
				'refer_to'	=> 0,
				'position'	=> 6
			],
			[//11
				'title'		=> 'Promotions',
				'slug'		=> '/admin/promo',
				'img'		=> 'fa-tags',
				'refer_to'	=> 10,
				'position'	=> 0
			],
			[//12
				'title'		=> 'Users',
				'slug'		=> '/admin/users',
				'img'		=> 'fa-user-o',
				'refer_to'	=> 0,
				'position'	=> 7
			],
			[//13
				'title'		=> 'Roles',
				'slug'		=> '/admin/users/roles',
				'img'		=> 'fa-users',
				'refer_to'	=> 12,
				'position'	=> 0
			],
		];
		foreach($data as $menu){
			if(AdminMenu::where('slug','=',$menu['slug'])->count() < 1){
				AdminMenu::create($menu);
				$this->info('Admin menu '.$menu['title'].' created successfully');
			}else{
				$this->line('Admin menu '.$menu['title'].' is already exists');
			}
		}
		//Default settings
		$data = [
			[
				'title'		=> 'E-mail',
				'slug'		=> 'text',
				'options'	=> '[]',
				'type'		=> 'main_info',
				'position'	=> 0
			],
			[
				'title'		=> 'Phone',
				'slug'		=> 'text',
				'options'	=> '[]',
				'type'		=> 'main_info',
				'position'	=> 1
			],
			[
				'title'		=> 'Address',
				'slug'		=> 'wysiwyg',
				'options'	=> '[]',
				'type'		=> 'main_info',
				'position'	=> 2
			],
			[
				'title'		=> 'Coordinates',
				'slug'		=> 'coordinates',
				'options'	=> '{"x":[],"y":[],"z":[]}',
				'type'		=> 'main_info',
				'position'	=> 3
			],
			[
				'title'		=> 'News',
				'slug'		=> 'news',
				'options'	=> json_encode([
					'category_type'	=> 0,
					'category_multiselect'=> 0,
					'slider'		=> 1,
					'description'	=>1,
					'text'			=>1,
					'tags'			=>1,
					'meta_data'		=>1,
					'seo_data'		=>1
				]),
				'type'		=> 'settings',
				'position'	=> 0
			],
			[
				'title'		=> 'Products',
				'slug'		=> 'products',
				'options'	=> json_encode([
					'category_type'	=> 0,
					'category_multiselect	'=> 0,
					'vendor_code'	=> 1,
					'quantity'		=> 1,
					'slider'		=> 1,
					'description'	=> 1,
					'text'			=> 1,
					'tags'			=> 1,
					'meta_data'		=> 1,
					'seo_data'		=> 1,
					'characteristics_table'	=> 1,
					'default_characteristics'	=> ''
				]),
				'type'		=> 'settings',
				'position'	=> 1
			],
			[
				'title'		=> 'Promotions',
				'slug'		=> 'promo',
				'options'	=> json_encode([
					'slider'		=> 1,
					'description'	=> 1,
					'text'			=> 1,
					'meta_data'		=> 1,
					'seo_data'		=> 1
				]),
				'type'		=> 'settings',
				'position'	=> 2
			],
		];
		foreach($data as $setting){
			if(Settings::where('type','=',$setting['type'])->where('position','=',$setting['position'])->count() < 1){
				Settings::create($setting);
				$this->info('Settings option '.$setting['title'].' created successfully');
			}else{
				$this->line('Settings option '.$setting['title'].' is already exists');
			}
		}

		//Users roles
		if(Roles::where('title','=','root')->count() < 1){
			Roles::create([
				'title'		=> 'root',
				'slug'		=> md5(uniqid()),
				'editable'	=> 0,
				'access_pages'=> 'grant_access',
				'created_by'=> 0,
				'updated_by'=> 0
			]);
			$this->info('Root role created successfully');
		}else{
			$this->line('Root role is already exists');
		}
		$this->info('Installation finished successfully');
	}
}
