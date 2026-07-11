<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePublicEngagementTables extends Migration
{
    public function up(): void
    {
        $this->createPosts();
        $this->createComments();
        $this->createReactions();
        $this->createEvents();
    }

    private function createPosts(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'platform' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'facebook'],
            'page_id' => ['type' => 'VARCHAR', 'constraint' => 120],
            'external_post_id' => ['type' => 'VARCHAR', 'constraint' => 190],
            'message' => ['type' => 'LONGTEXT', 'null' => true],
            'permalink_url' => ['type' => 'TEXT', 'null' => true],
            'post_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'published_at' => ['type' => 'DATETIME', 'null' => true],
            'raw_payload' => ['type' => 'LONGTEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['platform', 'external_post_id']);
        $this->forge->addKey(['platform', 'page_id']);
        $this->forge->createTable('social_posts');
    }

    private function createComments(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'social_post_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'parent_comment_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'external_comment_id' => ['type' => 'VARCHAR', 'constraint' => 190],
            'external_parent_id' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'author_external_id' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'author_name' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'message' => ['type' => 'LONGTEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'new'],
            'sentiment' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'category' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'priority' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'normal'],
            'requires_response' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'responded_at' => ['type' => 'DATETIME', 'null' => true],
            'commented_at' => ['type' => 'DATETIME', 'null' => true],
            'raw_payload' => ['type' => 'LONGTEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('external_comment_id');
        $this->forge->addKey('social_post_id');
        $this->forge->addKey(['status', 'requires_response']);
        $this->forge->addForeignKey('social_post_id', 'social_posts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('parent_comment_id', 'social_comments', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('social_comments');
    }

    private function createReactions(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'social_post_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'social_comment_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'external_object_id' => ['type' => 'VARCHAR', 'constraint' => 190],
            'actor_external_id' => ['type' => 'VARCHAR', 'constraint' => 190],
            'actor_name' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'reaction_type' => ['type' => 'VARCHAR', 'constraint' => 40],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'reacted_at' => ['type' => 'DATETIME', 'null' => true],
            'raw_payload' => ['type' => 'LONGTEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['external_object_id', 'actor_external_id']);
        $this->forge->addKey(['reaction_type', 'is_active']);
        $this->forge->addForeignKey('social_post_id', 'social_posts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('social_comment_id', 'social_comments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('social_reactions');
    }

    private function createEvents(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'platform' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'facebook'],
            'page_id' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'field_name' => ['type' => 'VARCHAR', 'constraint' => 80],
            'event_type' => ['type' => 'VARCHAR', 'constraint' => 80],
            'external_event_key' => ['type' => 'VARCHAR', 'constraint' => 190],
            'raw_payload' => ['type' => 'LONGTEXT'],
            'processed' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'processing_error' => ['type' => 'TEXT', 'null' => true],
            'processed_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('external_event_key');
        $this->forge->addKey(['processed', 'event_type']);
        $this->forge->createTable('social_engagement_events');
    }

    public function down(): void
    {
        $this->forge->dropTable('social_engagement_events', true);
        $this->forge->dropTable('social_reactions', true);
        $this->forge->dropTable('social_comments', true);
        $this->forge->dropTable('social_posts', true);
    }
}
