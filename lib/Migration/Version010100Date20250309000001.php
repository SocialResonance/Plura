<?php

declare(strict_types=1);

namespace OCA\Plura\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Migration for adding proposal credits table
 */
class Version010100Date20250309000001 extends SimpleMigrationStep {

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        // Create proposal_credits table
        if (!$schema->hasTable('plura_proposal_credits')) {
            $table = $schema->createTable('plura_proposal_credits');
            $table->addColumn('id', Types::BIGINT, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('proposal_id', Types::BIGINT, [
                'notnull' => true,
            ]);
            $table->addColumn('user_id', Types::STRING, [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('amount', Types::DECIMAL, [
                'notnull' => true,
                'precision' => 20,
                'scale' => 4,
            ]);
            $table->addColumn('created_at', Types::DATETIME, [
                'notnull' => true,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['proposal_id'], 'plura_propcredit_prop_idx');
            $table->addIndex(['user_id'], 'plura_propcredit_user_idx');
            $table->addUniqueConstraint(['proposal_id', 'user_id'], 'plura_propcredit_prop_user_unq');
            
            // Add foreign key to proposals table
            if ($schema->hasTable('plura_proposals')) {
                $table->addForeignKeyConstraint(
                    $schema->getTable('plura_proposals'),
                    ['proposal_id'],
                    ['id'],
                    ['onDelete' => 'CASCADE'],
                    'fk_plura_propcredit_prop_id'
                );
            }
        }

        return $schema;
    }
}