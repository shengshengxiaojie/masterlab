<?php

namespace main\app\ctrl;

use main\app\classes\ProjectLogic;
use main\app\model\OrgModel;
use main\app\model\project\ProjectModel;
use main\app\classes\UserLogic;
use main\app\classes\SettingsLogic;
use main\app\classes\ConfigLogic;
use main\lib\MySqlDump;

class Projects extends BaseUserCtrl
{

    public function __construct()
    {
        parent::__construct();
        parent::addGVar('top_menu_active', 'project');
    }

    /**
     * index
     */
    public function index()
    {
        $data = [];
        $data['title'] = '项目';
        $data['sub_nav_active'] = 'project';

        $dataKey = array(
            'count',
            'display_name'
        );

        $outProjectTypeList = [];
        $projectModel = new ProjectModel();
        $projectTypeAndCount = $projectModel->getAllProjectTypeCount();
        foreach ($projectTypeAndCount as $key => $value) {
            switch ($key) {
                case 'WHOLE':
                    $outProjectTypeList[0] = array_combine($dataKey, [$value, '全部']);
                    break;
                case 'SCRUM':
                    $outProjectTypeList[ProjectLogic::PROJECT_TYPE_SCRUM] =
                    array_combine($dataKey, [$value, ProjectLogic::$typeAll[ProjectLogic::PROJECT_TYPE_SCRUM]]);
                    break;
                case 'KANBAN':
                    $outProjectTypeList[ProjectLogic::PROJECT_TYPE_KANBAN] =
                    array_combine($dataKey, [$value, ProjectLogic::$typeAll[ProjectLogic::PROJECT_TYPE_KANBAN]]);
                    break;
                case 'SOFTWARE_DEV':
                    $outProjectTypeList[ProjectLogic::PROJECT_TYPE_SOFTWARE_DEV] =
                    array_combine($dataKey, [$value, ProjectLogic::$typeAll[ProjectLogic::PROJECT_TYPE_SOFTWARE_DEV]]);
                    break;
                case 'PROJECT_MANAGE':
                    $outProjectTypeList[ProjectLogic::PROJECT_TYPE_PROJECT_MANAGE] =
                    array_combine($dataKey, [$value, ProjectLogic::$typeAll[ProjectLogic::PROJECT_TYPE_PROJECT_MANAGE]]);
                    break;
                case 'FLOW_MANAGE':
                    $outProjectTypeList[ProjectLogic::PROJECT_TYPE_FLOW_MANAGE] =
                    array_combine($dataKey, [$value, ProjectLogic::$typeAll[ProjectLogic::PROJECT_TYPE_FLOW_MANAGE]]);
                    break;
                case 'TASK_MANAGE':
                    $outProjectTypeList[ProjectLogic::PROJECT_TYPE_TASK_MANAGE] =
                    array_combine($dataKey, [$value, ProjectLogic::$typeAll[ProjectLogic::PROJECT_TYPE_TASK_MANAGE]]);
                    break;
            }
        }

        $data['type_list'] = $outProjectTypeList;
        ConfigLogic::getAllConfigs($data);
        $this->render('gitlab/project/main.php', $data);
    }

    public function fetchAll($typeId = 0)
    {
        $typeId = intval($typeId);
        $projectModel = new ProjectModel();
        if($typeId){
            $projects = $projectModel->filterByType($typeId, false);
        }else{
            $projects = $projectModel->getAll(false);
        }

        $model = new OrgModel();
        $originsMap = $model->getMapIdAndPath();
        $types = ProjectLogic::$typeAll;
        foreach ($projects as &$item) {
            $item['type_name'] = isset($types[$item['type']]) ? $types[$item['type']] : '--';
            $item['path'] = isset($originsMap[$item['org_id']]) ? $originsMap[$item['org_id']] : 'default';
            $item['create_time_text'] = format_unix_time($item['create_time'], time());
            $item['create_time_origin'] = date('y-m-d H:i:s', $item['create_time']);
            $item['first_word'] = mb_substr(ucfirst($item['name']), 0, 1, 'utf-8');
            $item['bg_color'] = mapKeyColor($item['key']);
            list($item['avatar'], $item['avatar_exist']) = ProjectLogic::formatAvatar($item['avatar']);
        }

        $userLogic = new UserLogic();
        $data['users'] = $userLogic->getAllNormalUser();
        unset($userLogic, $item);

        $data['projects'] = $projects;
        $this->ajaxSuccess('success', $data);
    }

    public function test()
    {
        echo (new SettingsLogic)->dateTimezone();
    }




}
