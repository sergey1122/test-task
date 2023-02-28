<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PushNotificationTableQueue extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up(): void
    {
        $this->table('queues')
            ->addColumn('notification_id', 'integer')
            ->addColumn('user_id', 'integer')
            ->addColumn('token', 'string')
            ->addColumn('status', 'boolean', ['default' => 0])
            ->addTimestamps('created_at','sent_at')
            ->addIndex(['status'])
            ->addForeignKey(
                'user_id',
                'users',
                'id',
                [
                    'delete'=> 'NO_ACTION',
                    'update'=> 'NO_ACTION',
                    'constraint' => 'queues_user_id',
                ]
            )->addForeignKey(
                'notification_id',
                'push_notifications',
                'id',
                [
                    'delete'=> 'NO_ACTION',
                    'update'=> 'NO_ACTION',
                    'constraint' => 'queues_not_id',
                ]
            )
            ->create();
    }

    public function down(): void
    {
        $this->table('queues')
            ->drop();
    }
}
