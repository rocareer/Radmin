<?php


namespace app\controller;

class Index
{
	
	public function index()
	{
        try {
            if (!is_file(public_path() . '/install.lock') && is_file(base_path() . '/app/view/install' . DIRECTORY_SEPARATOR . 'index.html')) {
                return redirect('/install');
            }
            return view('index/index');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
	
}
