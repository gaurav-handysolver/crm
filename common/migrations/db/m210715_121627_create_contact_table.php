<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%contact}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 */
class m210715_121627_create_contact_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%contact}}', [
            'id' => $this->primaryKey(),
            'firstname' => $this->string(50),
            'lastname' => $this->string(50),
            'email' => $this->string(50),
            'company' => $this->string(50),
            'website' => $this->string(512),
            'mobile_number' => $this->string(20),
            'birthday' => $this->date(),
            'pollguru' => $this->boolean(),
            'buzz' => $this->boolean(),
            'learning_arcade' => $this->boolean(),
            'training_pipeline' => $this->boolean(),
            'leadership_edge' => $this->boolean(),
            'created_by' => $this->integer(11),
            'updated_at' => $this->timestamp(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // creates index for column `created_by`
        $this->createIndex(
            '{{%idx-contact-created_by}}',
            '{{%contact}}',
            'created_by'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-contact-created_by}}',
            '{{%contact}}',
            'created_by',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-contact-created_by}}',
            '{{%contact}}'
        );

        // drops index for column `created_by`
        $this->dropIndex(
            '{{%idx-contact-created_by}}',
            '{{%contact}}'
        );

        $this->dropTable('{{%contact}}');
    }
}
