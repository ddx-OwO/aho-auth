<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(!function_exists('css_path'))
{
	function css_path($file, $type='general', $template_name='')
	{
		switch($type)
		{
			case 'general':
				return str_replace('\\', '/', base_url('assets/css/'.$file));
			break;
			
			case 'template':
				return str_replace('\\', '/', base_url('assets/templates/'.$template_name.'/css/'.$file));
			break;
			
			default:
				return str_replace('\\', '/', base_url('assets/css/'.$file));
		}
	}
}

if(!function_exists('js_path'))
{
	function js_path($file, $type='general', $template_name='')
	{
		switch($type)
		{
			case 'general':
				return str_replace('\\', '/', base_url('assets/js/'.$file));
			break;
			
			case 'template':
				return str_replace('\\', '/', base_url('assets/templates/'.$template_name.'/js/'.$file));
			break;
			
			default:
				return str_replace('\\', '/', base_url('assets/js/'.$file));
		}
	}
}

if(!function_exists('font_path'))
{
	function font_path($file, $type='general', $template_name='')
	{
		switch($type)
		{
			case 'general':
				return str_replace('\\', '/', base_url('assets/fonts/'.$file));
			break;
			
			case 'template':
				return str_replace('\\', '/', base_url('assets/templates/'.$template_name.'/fonts/'.$file));
			break;
			
			default:
				return str_replace('\\', '/', base_url('assets/fonts/'.$file));
		}
	}
}

if(!function_exists('img_path'))
{
	function img_path($file, $type='general', $template_name='')
	{
		switch($type)
		{
			case 'general':
				return str_replace('\\', '/', assets_path('images/'.$file));
				break;
			
			case 'template':
				return str_replace('\\', '/', base_url('assets/templates/'.$template_name.'/images/'.$file));
				break;
			
			default:
				return str_replace('\\', '/', assets_path('images/'.$file));
		}
	}
}

if(!function_exists('template_asset'))
{
	function template_asset($path, $template_name='') 
	{
		return str_replace('\\', '/', assets_path('/templates/'.$template_name.'/vendor/'.$path));
	}
}

if(!function_exists('assets_path'))
{
	/*
	* Get assets storage folder
	*
	* @param $file File name
	* @return string
	*/
	function assets_path($file)
	{
		return str_replace('\\', '/', base_url('assets/'.$file));
	}
}

?>