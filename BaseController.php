<?php

namespace admin\Controller;

use Engine\DI\DI;
use Engine\Helper\SearchFileFolder;
use Engine\Helper\SimpleCSV;
use Dotenv\Dotenv;


class BaseController extends AdminController
{
    private $baseModel;

    /**
     * BaseController constructor.
     * @param $di
     */
    public function __construct(DI $di)
    {
        parent::__construct($di);

        if (\Core_UserData::getRole() == 2) {
            header('Location:  ' . \Core_LinkProxy::getLink());
            exit;
        }

        $this->baseModel = $this->load->model('Base\Base');
    }

    /**
     * @return bool|string
     */
    public function checkFile()
    {
        sleep(1);
        $pathFile = SearchFileFolder::find(
            $this->config['PATH_FILE_BASE'],
            $this->config['FILE_NAME_BASES'],
            $this->config['FILE_TYPE']
        );

        $result = is_file($pathFile) ? $pathFile : false;
        echo $result;

        return $result;
    }


    /**
     * @param $pathFile
     */
    protected function uploadBaseXLS($pathFile)
    {
        $this->SimpleXLSX->readFile($pathFile);
        $sheet = $this->SimpleXLSX->rows(1);

        $userID = $this->request->session['id'];
        $this->baseModel->repository->saveUploadDate($userID, $pathFile, 'products');

        $count = 0;
        foreach ($sheet as $row) {
            $idItem = '';
            if ($count > 0) {
                $baseArray[] = [
                    'title' => $row[1],
                    'description' => $row[9],
                    'roles' => $row[5],
                    'links' => $row[6]
                ];
            }
            $count++;
        }

        $this->baseModel->repository->saveBase($baseArray);
        $this->logs->updateBase('knobase');
        unset($baseArray);
    }

    /**
     * @param $pathFile
     */
    protected function uploadFileBase($pathFile)
    {
        $data = \Core_CSV::getData($pathFile);

        if (is_array($data) && count($data) > 0) {
            $this->baseModel->repository->clearAttachments();
            foreach ($data as $item) {
                $productID = preg_replace('/[^0-9]/', '', $item['refersTo']);
                $test = preg_match(
                    '/^' . $this->config['KEY_WORD_ATTACHMENTS_REFERS_BASES'] . '/',
                    $item['refersTo'],
                    $matches
                );

                if (count($matches) > 0) {
                    $this->baseModel->repository->saveAttachments(
                        $item['title'],
                        $item['filename'],
                        $item['extension'],
                        $item['refersTo'],
                        $item['type'],
                        $item['description'],
                        $productID
                    );
                }
            }
            unlink($pathFile);
        }
    }

    /**
     * @param $pathFile
     */
    protected function uploadFileContact($pathFile)
    {
        $data = \Core_CSV::getData($pathFile);

        if (is_array($data) && count($data) > 0) {
            $this->baseModel->repository->clearContacts();

            foreach ($data as $item) {
                $this->baseModel->repository->saveContacts(
                    $item['position'],
                    $item['fio'],
                    $item['phone'],
                    $item['e-mail'],
                    $item['refers_to']
                );
            }
            unlink($pathFile);
        }
    }


    /**
     *
     */
    public function fileUpload()
    {
        if (isset($this->request->post['uploads'])) {
            if (strlen($_FILES['products']['name']) > 0) {
                $image = $_FILES['products'];
                $fileTmpName = $_FILES['products']['tmp_name'];
                $fileName = $_FILES['products']['name'];
                $errorCode = $_FILES['products']['error'];

                $pathFile = $this->config['PATH_FILE_UPLOAD'];
                if (!is_dir($pathFile)) {
                    mkdir($pathFile, 0777, true);
                }

                $pathFile = $pathFile . $_FILES['products']['name'];
                move_uploaded_file($fileTmpName, $pathFile);
            }
        }

        if (!isset($pathFile)) {
            ob_start();
            if ($this->checkFile() !== false) {
                $pathFile = $this->checkFile();
            }
            ob_end_clean();
        }

        if (isset($pathFile)) {
            $lastFileUpdate = $this->baseModel->repository->getUploadDate('products');
            $baseArray = [];
            $this->baseModel->repository->delUploadDate('products');
            $this->baseModel->repository->clearBase();
            $dataFiles = \Core_Copyfile::copy(
                $this->config['PATH_FILE_BASE'],
                $this->config['FOLDER_UPLOAD'],
                ['jpg', 'png', 'pdf', 'pptx', 'xls', 'xlsx']
            );

            if ($this->config['FILE_TYPE'] == 'xls') {
                $this->uploadBaseXLS($pathFile);
            }

            if ($this->config['FILE_TYPE'] == 'csv') {
                $data = \Core_CSV::getData($pathFile);

                if ($data) {
                    foreach ($data as $row) {
                        if (strlen($row['description']) <= 1) {
                            $row['description'] = "Описание";
                        }

                        $tempName = "/^" . $this->config['FILE_NAME_BASES'] . "\[" . $row['id'] . "\].*/i";
                        $filesLink = '';

                        foreach ($dataFiles as $val) {
                            if (preg_match($tempName, $val, $matches)) {
                                $filesLink .= $val . ";";
                            }
                        }

                        $baseArray[] = [
                            'title' => $row['name'],
                            'description' => $row['description'],
                            'fileslink' => '',
                            'roles' => '',
                            'links' => '',
                            'id_project' => $row['id'],
                            'crm_id' => $row['crm_id'],
                            'status' => $row['status'],
                            'segment' => $row['segment'],
                            'nameDzo' => $row['nameDzo'],
                            'roleUpr' => $row['roleUpr']
                        ];
                    }

                    $this->baseModel->repository->saveBase($baseArray);
//                    \Core_UserData::getSudirRoles()
//                    $arrayData, $sudirID, $sudirLogin, $sudirRoles

                    $arrayLogs = [
                        "knobase" => $baseArray,
                        "dataFiles" => $dataFiles
                    ];


                    $userID = $this->request->session['id'];
                    $this->baseModel->repository->saveUploadDate($userID, $pathFile, 'products');
                    $this->logs->updateBase(
                        'knobase',
                        $arrayLogs,
                        \Core_UserData::getSudirID(),
                        \Core_UserData::getSudirLogin(),
                        \Core_UserData::getSudirRoles()
                    );

                    unset($baseArray);
                }
            }
            @unlink($pathFile);
        }

        $filePath = $this->config['PATH_FILE_BASE'] . $this->config['FILE_NAME_LISTFILES'] . "." . $this->config['FILE_TYPE'];
        $this->uploadFileBase($filePath);

        $pathFiles = $this->config['PATH_FILE_BASE'] . $this->config['FILE_NAME_CONTACT'] . "." . $this->config['FILE_TYPE'];
        $this->uploadFileContact($pathFiles);


        header("Location: " . \Core_LinkProxy::getLink() . "/admin/products/fileUpload/");
    }


    /**
     *
     */
    public function listing()
    {
        $query['products'] = $this->baseModel->repository->getList();

        $this->view->render('base/listing', $query);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $id = trim(htmlspecialchars(stripcslashes($id)));

        if (isset($id) && !empty($id) && $id > 0) {
            $base = $this->baseModel->repository->getBase($id);
            $this->logs->dropProject('knobase', $id, $base);

            return $this->baseModel->repository->deleteBase($id);
        }
    }


    /**
     *
     */
    public function directoryLoadPage()
    {
        $query['products'] = $this->baseModel->repository->getList();
        $query['directory'] = $this->baseModel->repository->getUploadDate('products');
        $query['config'] = $this->config;

//        var_dump($query['directory']);

        $this->view->render('base/directoryLoad', $query);
    }


    public function saveImage()
    {
        error_reporting(E_ALL | E_STRICT);
        //require('UploadHandler.php');
        $upload_handler = new \Engine\Helper\UploadHandler(
            [
                'upload_dir' => 'W:/OSPanel/domains/terriotory/uploads/',
                'upload_url' => 'http://pilots.cc/uploads/'
            ],
            true
        );
    }

    public function deleteImage()
    {
        $params = $this->request->post;

        if (isset($params['newsID']) && strlen($params['newsID']) > 0) {
            $value = '';

            $this->db->query("UPDATE news SET image = '$value' WHERE id = " . $params['newsID']);
        }
    }

    public function exportPDF()
    {
        if (isset($_GET['dateload'])) {
            $dateload = trim(htmlspecialchars(stripcslashes($_GET['dateload'])));
        }

        if ($dateload !== '') {
            header("location: /exportNews.php?dateload=$dateload");
        } else {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }
}