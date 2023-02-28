<?php


namespace app\controllers;


use app\models\PushNotificationsModel;
use app\models\QueuesModel;

class PushNotificationsController
{
    /**
     * @api {post} / Request to send
     *
     * @apiVersion 0.1.0
     * @apiName send
     * @apiDescription This method saves the push notification and put it to the queue.
     * @apiGroup Sending
     *
     * @apiParam {string="send"} action API method
     * @apiParam {string} title Title of push notification
     * @apiParam {string} message Message of push notification
     * @apiParam {int} country_id Country ID
     *
     * @apiParamExample {json} Request-Example:
    {"action":"send","title":"Hello","message":"World","country_id":4}
     *
     * @apiSuccessExample {json} Success:
    {"success":true,"result":{"notification_id":123}}
     *
     * @apiErrorExample {json} Failed:
    {"success":false,"result":null}
     */
    public function sendByCountryId(string $title, string $message, int $countryId): ?array
    {

        // TODO create a push notification in the database and return notification ID:
        $model = new PushNotificationsModel();
        $notification_id =  $model->saveNotificationsByCountryId($title,$message,$countryId);
        if($notification_id){
            return ['notification_id'=>$notification_id];
        }else{
            return null;
        }

    }

    /**
     * @api {post} / Get details
     *
     * @apiVersion 0.1.0
     * @apiName details
     * @apiDescription This method returns all details by notification ID.
     * @apiGroup Information
     *
     * @apiParam {string="details"} action API method
     * @apiParam {int} notification_id Notification ID
     *
     * @apiParamExample {json} Request-Example:
    {"action":"details","notification_id":123}
     *
     * @apiSuccessExample {json} Success:
    {"success":true,"result":{"id":123,"title":"Hello","message":"World","sent":90000,"failed":10000,"in_progress":100000,"in_queue":123456}}
     *
     * @apiErrorExample {json} Notification not found:
    {"success":false,"result":null}
     */
    public function details(int $notificationID): ?array
    {
        // TODO return an array like:
        $model = new PushNotificationsModel();
        $result = $model->getNotification($notificationID);

        return $result;
    }

    /**
     * @api {post} / Sending by CRON
     *
     * @apiVersion 0.1.0
     * @apiName cron
     * @apiDescription This method sends the push notifications from queue.
     * @apiGroup Sending
     *
     * @apiParam {string="cron"} action API method
     *
     * @apiParamExample {json} Request-Example:
    {"action":"cron"}
     *
     * @apiSuccessExample {json} Success and sent:
    {"success":true,"result":[{"notification_id":123,"title":"Hello","message":"World","sent":50000,"failed":10000},{"notification_id":124,"title":"New","message":"World","sent":20000,"failed":20000}]}
     *
     * @apiSuccessExample {json} Success, no notifications in the queue:
    {"success":true,"result":[]}
     */
    public function cron(): array
    {
        //start queue
        //check if we have no queue in progress create one if no return message
        $limit = 100000;
        $model = new PushNotificationsModel();
        $result =  $model->sendPushNotifications($limit);
        return $result;
    }
}