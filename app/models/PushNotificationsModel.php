<?php


namespace app\models;


use Exception;
use app\models\DB;
use PDO;
class PushNotificationsModel
{
    const PENDING = 0;
    const IN_PROGRESS = 1;
    const FAILED = 2;
    const SUCCESS = 3;

    /**
     * @throws Exception
     */
    public static function send(string $title, string $message, string $token): bool
    {
        return random_int(1, 10) > 1;
    }

     public function saveNotificationsByCountryId(string $title, string $message, int $countryId): ?int
     {
         //prepare db connection
         $db = new DB();
         $pdo = $db->conn;
         //insert data in to push notifications table
         $insert = "INSERT INTO push_notifications (title, message, country_id) VALUES (:title, :message, :country_id)";
         $stmt = $pdo->prepare($insert);
         $stmt->execute([':title'=>$title,':message'=>$message,':country_id'=>$countryId]);
         $notification_id = $pdo->lastInsertId();


         //get all users and user devices for country which are not expired
         $sql = "select d.token, u.id as user_id  from users as u 
                 LEFT JOIN devices as d on d.user_id=u.id
                 where d.expired = 0 and u.country_id = ?
                ";
         $stmt = $pdo->prepare($sql);
         $stmt->execute([$countryId]);
         $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
         //store data in to queues
         $status = self::PENDING;
         foreach($devices as $key=>$val){
             $data[] = "('{$val['token']}',{$val['user_id']},{$notification_id},{$status})";
         }
         $values = implode(', ',$data);
         substr($values, 0, -1);

         $insertSql = "INSERT INTO queues (`token`,`user_id`,`notification_id`, `status`) VALUES {$values}";
         $pdo->prepare($insertSql)->execute();

         return $notification_id;

     }

     public function sendPushNotifications(int $limit):?array
     {
         $db = new DB();
         $pdo = $db->conn;


         $sql = "select q.*, pn.title,pn.message from queues as q 
                    left join push_notifications as pn on pn.id=q.notification_id
                    where status=? LIMIT {$limit}";

         $stmt = $pdo->prepare($sql);
         $stmt->execute([self::PENDING]);
         $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
         if (!empty($devices)) {
             $pdo->beginTransaction();
             $sql = "UPDATE queues SET status=? where id=?";
             $stmt = $pdo->prepare($sql);
             foreach ($devices as $key => $value) {
                 if(!isset($response[$value['notification_id']])){
                     $response[$value['notification_id']] = [
                         'notification_id'=>$value['notification_id'],
                         'title'=>$value['title'],
                         'message'=>$value['message'],
                         'sent' => 0,
                         'failed' => 0
                     ];
                 }
                 if (self::send($value['title'], $value['message'], $value['token'])) {
                     //if push notification sent
                     $status = self::SUCCESS;
                     $response[$value['notification_id']]['sent']++;
                 }
                 else{
                     //if sending failed
                     $status = self::FAILED;
                     $response[$value['notification_id']]['failed']++;
                     }
                 //update queue status and sent_date

                 $stmt->execute([$status, $value['id']]);

             }
             $pdo->commit();
             $result = array_values($response);
             return $result;
         }
         else{
                 return array();
             }

         }


    public function getNotification(int $notificationID): ?array
    {
        $failed = self::FAILED;
        $sent = self::SUCCESS;
        $in_queue = self::PENDING;
        $in_progress = self::IN_PROGRESS;

        //query for getting counts for each status
        $failed_count = " (select count(id) from queues where notification_id = {$notificationID} and status={$failed}) as failed";
        $sent_count = " (select count(id) from queues where notification_id = {$notificationID} and status={$sent}) as sent ";
        $queue_count = " (select count(id) from queues where notification_id = {$notificationID} and status={$in_queue}) as in_queue ";
        $in_progress_count = " (select count(id) from queues where notification_id = {$notificationID} and status={$in_progress}) as in_progress ";

        $sql = "select id, title, message, {$failed_count},$sent_count,$queue_count,$in_progress_count from push_notifications 
                    where id=? ";
        $db = new DB();
        $pdo = $db->conn;
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$notificationID]);
        $notification = $stmt->fetch(PDO::FETCH_ASSOC);

           if(!$notification){
               return null;
           }else{
               return $notification;
           }

    }
}