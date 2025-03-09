<?php

declare(strict_types=1);

namespace OCA\Plura\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Initial database schema for the Plura app
 */
class Version010000Date20250309000000 extends SimpleMigrationStep {

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        // Create proposals table
        if (!$schema->hasTable('plura_proposals')) {
            $table = $schema->createTable('plura_proposals');
            $table->addColumn('id', Types::BIGINT, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('title', Types::STRING, [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('description', Types::TEXT, [
                'notnull' => true,
            ]);
            $table->addColumn('document_id', Types::STRING, [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('status', Types::STRING, [
                'notnull' => true,
                'length' => 50,
                'default' => 'open',
            ]);
            $table->addColumn('credits_allocated', Types::DECIMAL, [
                'notnull' => true,
                'precision' => 20,
                'scale' => 4,
                'default' => 0,
            ]);
            $table->addColumn('created_at', Types::DATETIME, [
                'notnull' => true,
            ]);
            $table->addColumn('deadline', Types::DATETIME, [
                'notnull' => false,
            ]);
            $table->addColumn('user_id', Types::STRING, [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['user_id'], 'plura_proposals_user_id_idx');
            $table->addIndex(['document_id'], 'plura_proposals_doc_id_idx');
            $table->addIndex(['status'], 'plura_proposals_status_idx');
        }

        // Create implementations table
        if (!$schema->hasTable('plura_implementations')) {
            $table = $schema->createTable('plura_implementations');
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
            $table->addColumn('content', Types::TEXT, [
                'notnull' => true,
            ]);
            $table->addColumn('status', Types::STRING, [
                'notnull' => true,
                'length' => 50,
                'default' => 'pending',
            ]);
            $table->addColumn('created_at', Types::DATETIME, [
                'notnull' => true,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['proposal_id'], 'plura_impl_proposal_id_idx');
            $table->addIndex(['user_id'], 'plura_impl_user_id_idx');
            $table->addIndex(['status'], 'plura_impl_status_idx');
            $table->addForeignKeyConstraint(
                $schema->getTable('plura_proposals'),
                ['proposal_id'],
                ['id'],
                ['onDelete' => 'CASCADE'],
                'fk_plura_impl_proposal_id'
            );
        }

        // Create votes table
        if (!$schema->hasTable('plura_votes')) {
            $table = $schema->createTable('plura_votes');
            $table->addColumn('id', Types::BIGINT, [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('implementation_id', Types::BIGINT, [
                'notnull' => true,
            ]);
            $table->addColumn('user_id', Types::STRING, [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('vote_type', Types::STRING, [
                'notnull' => true,
                'length' => 10,
            ]);
            $table->addColumn('vote_weight', Types::DECIMAL, [
                'notnull' => true,
                'precision' => 10,
                'scale' => 2,
                'default' => 1,
            ]);
            $table->addColumn('prediction_amount', Types::DECIMAL, [
                'notnull' => false,
                'precision' => 20,
                'scale' => 4,
            ]);
            $table->addColumn('created_at', Types::DATETIME, [
                'notnull' => true,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['implementation_id'], 'plura_votes_impl_id_idx');
            $table->addIndex(['user_id'], 'plura_votes_user_id_idx');
            $table->addUniqueConstraint(['implementation_id', 'user_id'], 'plura_votes_impl_user_unique');
            $table->addForeignKeyConstraint(
                $schema->getTable('plura_implementations'),
                ['implementation_id'],
                ['id'],
                ['onDelete' => 'CASCADE'],
                'fk_plura_votes_impl_id'
            );
        }

        // Create user_credits table
        if (!$schema->hasTable('plura_user_credits')) {
            $table = $schema->createTable('plura_user_credits');
            $table->addColumn('user_id', Types::STRING, [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('credit_amount', Types::DECIMAL, [
                'notnull' => true,
                'precision' => 20,
                'scale' => 4,
                'default' => 100, // Default initial credits
            ]);
            $table->addColumn('last_updated', Types::DATETIME, [
                'notnull' => true,
            ]);
            $table->setPrimaryKey(['user_id']);
        }

        // Create credit_transactions table
        if (!$schema->hasTable('plura_credit_transactions')) {
            $table = $schema->createTable('plura_credit_transactions');
            $table->addColumn('id', Types::BIGINT, [
                'autoincrement' => true,
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
            $table->addColumn('transaction_type', Types::STRING, [
                'notnull' => true,
                'length' => 50,
            ]);
            $table->addColumn('related_entity_id', Types::BIGINT, [
                'notnull' => false,
            ]);
            $table->addColumn('created_at', Types::DATETIME, [
                'notnull' => true,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['user_id'], 'plura_transactions_user_idx');
            $table->addIndex(['transaction_type'], 'plura_transactions_type_idx');
            $table->addIndex(['created_at'], 'plura_transactions_date_idx');
        }

        // Create matching_fund table
        if (!$schema->hasTable('plura_matching_fund')) {
            $table = $schema->createTable('plura_matching_fund');
            $table->addColumn('id', Types::BIGINT, [
                'autoincrement' => true,
                'notnull' => true, 
            ]);
            $table->addColumn('total_amount', Types::DECIMAL, [
                'notnull' => true,
                'precision' => 20,
                'scale' => 4,
                'default' => 0,
            ]);
            $table->addColumn('last_distribution_date', Types::DATETIME, [
                'notnull' => false,
            ]);
            $table->setPrimaryKey(['id']);
        }

        // Create admin_parameters table
        if (!$schema->hasTable('plura_admin_parameters')) {
            $table = $schema->createTable('plura_admin_parameters');
            $table->addColumn('parameter_name', Types::STRING, [
                'notnull' => true,
                'length' => 100,
            ]);
            $table->addColumn('parameter_value', Types::TEXT, [
                'notnull' => true,
            ]);
            $table->addColumn('last_updated', Types::DATETIME, [
                'notnull' => true,
            ]);
            $table->setPrimaryKey(['parameter_name']);
        }

        return $schema;
    }

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     */
    public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
        // Initialize the admin parameters with default values
        $this->initializeParameters();
    }

    private function initializeParameters(): void {
        $now = new \DateTime();
        $formattedDate = $now->format('Y-m-d H:i:s');
        
        $defaultParameters = [
            [
                'parameter_name' => 'k_value',
                'parameter_value' => '0.25',
                'last_updated' => $formattedDate
            ],
            [
                'parameter_name' => 'initial_credit_amount',
                'parameter_value' => '100',
                'last_updated' => $formattedDate
            ],
            [
                'parameter_name' => 'matching_fund_percentage',
                'parameter_value' => '25',
                'last_updated' => $formattedDate
            ],
            [
                'parameter_name' => 'minimum_vote_threshold',
                'parameter_value' => '5',
                'last_updated' => $formattedDate
            ],
            [
                'parameter_name' => 'proposal_funding_period_days',
                'parameter_value' => '14',
                'last_updated' => $formattedDate
            ],
            [
                'parameter_name' => 'implementation_voting_period_hours',
                'parameter_value' => '72',
                'last_updated' => $formattedDate
            ]
        ];

        // Initialize the matching fund
        $matchingFund = [
            'total_amount' => 1000, // Initial matching fund amount
            'last_distribution_date' => $formattedDate
        ];

        $db = \OC::$server->getDatabaseConnection();
        
        // Insert default parameters
        foreach ($defaultParameters as $param) {
            $db->insertIfNotExist('*PREFIX*plura_admin_parameters', $param, ['parameter_name']);
        }
        
        // Insert matching fund
        $db->insertIfNotExist('*PREFIX*plura_matching_fund', $matchingFund, ['id']);
    }
}