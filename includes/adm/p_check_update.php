<?php
/**
*
* @package adm
* @copyright (c) 2007 Kleeja.com
* @license ./docs/license.txt
*
*/

// not for directly open
if (! defined('IN_ADMIN'))
{
    exit();
}

$stylee	     = 'admin_check_update';
$current_smt	= preg_replace('/[^a-z0-9_]/i', '', g('smt', 'str', 'general'));
$update_link = $config['siteurl'] . 'install/update.php?lang=' . $config['language'];

//to prevent getting the url data for all cats
if ($current_smt == 'check'):

    //get data from kleeja github repo
    if (! ($version_data = $cache->get('kleeja_repo_version')))
    {
        $github_data = fetch_remote_file('https://raw.githubusercontent.com/awssat/kleeja/master/includes/version.php', false, 6);

        if (! empty($github_data))
        {
            preg_match_all('/define\(\'KLEEJA_VERSION\',\s{1,4}\'([^\']+)\'\);/', $github_data, $matches, PREG_SET_ORDER, 0);
            $version_data = trim(htmlspecialchars($matches[0][1]));
            $cache->save('kleeja_repo_version', $version_data, 3600 * 2);
        }
    }

    $error = 0;

    if (empty($version_data))
    {
        $text  = $lang['ERROR_CHECK_VER'];
        $error = 1;
    }
    else
    {
        if (version_compare(strtolower(KLEEJA_VERSION), strtolower($version_data), '<'))
        {
            $text	 = sprintf($lang['UPDATE_NOW_S'], KLEEJA_VERSION, strtolower($version_data)) . '<br /><br />' . $lang['UPDATE_KLJ_NOW'];
            $error	= 1;
        }
        elseif (version_compare(strtolower(KLEEJA_VERSION), strtolower($version_data), '='))
        {
            $text	= $lang['U_LAST_VER_KLJ'];
        }
        elseif (version_compare(strtolower(KLEEJA_VERSION), strtolower($version_data), '>'))
        {
            $text	= $lang['U_USE_PRE_RE'];
        }
        else
        {
            $text = $lang['ERROR_CHECK_VER'] . ' [code: ' . htmlspecialchars($version_data) . ']';
        }
    }

    $data	= [
        'version_number'	=> $version_data,
        'last_check'		   => time()
    ];

    $data = serialize($data);

    update_config('new_version', $SQL->real_escape($data), false);
    delete_cache('data_config');

    $adminAjaxContent = $error . ':::' . $text;

elseif ($current_smt == 'general'):

// if(!$error)
// {



    //To prevent expected error [ infinit loop ]
    if (ig('show_msg'))
    {
        $query_get	= [
            'SELECT'	=> '*',
            'FROM'		 => "{$dbprefix}config",
            'WHERE'		=> "name = 'new_version'"
        ];

        $result_get =  $SQL->build($query_get);

        if (! $SQL->num_rows($result_get))
        {
            //add new config value
            add_config('new_version', '');
        }
    }


// }

$showMessage = ig('show_msg');


//end current_smt == general
endif;

//secondary menu
$go_menu = [
    'general' => ['name'=>$lang['R_CHECK_UPDATE'], 'link'=> basename(ADMIN_PATH) . '?cp=p_check_update&amp;smt=general', 'goto'=>'general', 'current'=> $current_smt == 'general'],
    'howto'   => ['name'=>$lang['HOW_UPDATE_KLEEJA'], 'link'=> basename(ADMIN_PATH) . '?cp=p_check_update&amp;smt=howto', 'goto'=>'howto', 'current'=> $current_smt == 'howto'],
    'site'    => ['name'=>'Kleeja.com', 'link'=> 'http://www.kleeja.com', 'goto'=>'site', 'current'=> $current_smt == 'site'],
];
