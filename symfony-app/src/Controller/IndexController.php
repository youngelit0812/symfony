<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Url_store;
use App\Form\FormValidationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Form\Extension\Core\Type\FileType; 
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Service\FileUploader;
use Psr\Log\LoggerInterface;

class IndexController extends AbstractController
{
     /**
     * @Route("/index", name="app_post_index", methods={"POST"})
     * @param Request $request
     * @param string $uploadDir
     * @param FileUploader $uploader
     * @param LoggerInterface $logger
     * @return Response
     */
    public function index_post(Request $request, string $uploadDir,
                         FileUploader $uploader, LoggerInterface $logger): Response
    {
        try{
            $uid = 0;

            $cur_token = $this->get('security.token_storage')->getToken();        
            if ($cur_token) {
                $user = $cur_token->getUser();            
                if ($user && ($user instanceof User)) {
                    $uid = $user->getId();                
                }
            }
            
            if ($uid === 0 ) return $this->redirectToRoute('app_login');

            $file = $request->files->get('fileToUpload');
            if (empty($file)) {
                return $this->render('index/index.html.twig', [
                    'load_duration' => 0,
                    'inserted_record_cnt' => 0,
                    'file_path' => 'none',
                ]);
            }

            $config_update_cmd_array = array("mysqld --innodb_buffer_pool_size=4G",
                                        "mysqld --innodb_log_buffer_size=256M",
                                        "mysqld --innodb_write_io_threads=48",
                                        "mysqld --innodb_log_file_size=2G",                                        
                                        "mysqld --innodb_flush_log_at_trx_cmmit=0",
                                        "mysqld --innodb_doublewrite=0",
                                        );

            $command_count = count($config_update_cmd_array);
            for ($i = 0; $i < $command_count; $i++) {                           
                exec($config_update_cmd_array[$i]);
            }

            $filename = $file->getClientOriginalName();    
            $uploader->upload($uploadDir, $file, $filename);

            $uploadedFilePath = realpath($uploadDir."/".$filename);
            $uploadedFilePath = str_replace("\\", "\\\\", $uploadedFilePath);        
            
            $entityManager = $this->getDoctrine()->getManager();
            $conn = $entityManager->getConnection();            
            $UrlStoreRepository = $entityManager->getRepository(Url_store::class);            
            $org_record_cnt = $UrlStoreRepository->getCountOfRecordsWithCondition($uid);

            $begin_time = microtime(true);
            
            $sql = "LOAD DATA CONCURRENT INFILE '".$uploadedFilePath."' IGNORE INTO TABLE url_store 
                    COLUMNS TERMINATED BY '	' OPTIONALLY ENCLOSED BY '\"' 
                    LINES TERMINATED BY '\n' (@var1) SET 
                    i_dns=IF (STRCMP(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(@var1, '://', -1), '/', 1), ':', -1), '80')=0, SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(@var1, '://', -1), '/', 1), ':', 1), SUBSTRING_INDEX(SUBSTRING_INDEX(@var1, '://', -1), '/', 1)), 
                    i_uri=SUBSTRING_INDEX(SUBSTR(SUBSTRING_INDEX(@var1, '://', -1), POSITION('/' IN SUBSTRING_INDEX(@var1, '://', -1)) + 1), '?', 1),
                    i_parameter=IF (STRCMP(SUBSTRING_INDEX(@var1, '?', -1), @var1)=0, '', SUBSTRING_INDEX(@var1, '?', -1)),
                    uid=".$uid;
            
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            $load_duration = (int)(microtime(true) - $begin_time);
            $inserted_record_cnt = $UrlStoreRepository->getCountOfRecordsWithCondition($uid) - $org_record_cnt;
        }catch(Exception $e) {}        
        
        return $this->render('index/index.html.twig', [
            'load_duration' => $load_duration,
            'inserted_record_cnt' => $inserted_record_cnt,
            'file_path' => $filename,
        ]);
    }

    /**
     * @Route("/index", name="app_index", methods={"GET"})
     */
    public function index(Request $request) : Response
    {
        $cur_token = $this->get('security.token_storage')->getToken();       
        if ($cur_token) {
            $user = $cur_token->getUser();            
            if (!$user || !($user instanceof User)) {
                return $this->redirectToRoute('app_login');
            }
        }
                
        return $this->render('index/index.html.twig', [
            'load_duration' => 0,
            'inserted_record_cnt' => 0,
            'file_path' => 'no',
        ]);
    }
}
