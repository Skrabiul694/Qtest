<?php
/**
 * File name: AppInstallController.php
 * Last modified: 19/07/21, 3:10 PM
 * Author: NearCraft - https://codecanyon.net/user/nearcraft
 * Copyright (c) 2021
 */

namespace App\Http\Controllers;

use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Artisan;

class AppInstallController extends Controller
{
    /**
     * Update env file, configure storage and cache after installation
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function onSuccessfulInstall()
    {
        $installed = file_exists(storage_path('installed'));

        if($installed) {
            $env = DotenvEditor::load();
            $env->addEmpty();
            $env->setKey('APP_INSTALLED', 'true');
            $env->setKey('APP_VERSION', '1.3.0');
            $env->setKey('SESSION_DRIVER', 'database');
            $env->setKey('SESSION_LIFETIME', '43200');
            $env->save();

            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('storage:link');
        }

        return redirect()->route('welcome');
    }

    /**
     * Go to migration screen
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function migrate()
    {
        if(config('qtest.version') == '1.3.0') {
            return response()->json([
                'error' => 404,
                'message' => 'Nothing to migrate. App is already up to date.'
            ], 404);
        }
        return view('migration', [
            'success' => false,
            'message' => 'If you updated the app with latest files, run migrations by clicking Migrate Now button.'
        ]);
    }

    /**
     * Force run migrations after update
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function runMigrations()
    {
        $installed = file_exists(storage_path('installed'));

        if($installed) {
            if(config('qtest.version') == '1.3.0') {
                return response()->json([
                    'error' => 404,
                    'message' => 'Nothing to migrate. App is already up to date.'
                ], 404);
            }
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force'=> true]);
            return view('migration', [
                'success' => true,
                'message' => 'Migration successful. Now, login and fix updates in the maintenance settings.'
            ]);
        }

        return response()->json([
            'error' => 404,
            'message' => 'App is not yet installed.'
        ], 404);
    }
}
