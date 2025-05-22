<?php

declare(strict_types=1);

namespace Laradom\ORM\Mapping\Processor\PostProcessor;

use Laradom\ORM\Mapping\EntityMetadata;

class MetadataValidationPostProcessor implements EntityMetadataPostProcessorInterface
{
    private array $errors = [];

    /**
     * @param EntityMetadata[] $allMetadata
     *
     * @return EntityMetadata[]
     */
    public function process(array $allMetadata): array
    {
        $this->errors = [];

        foreach ($allMetadata as $metadata) {
            $this->validateEntityMetadata($metadata, $allMetadata);
        }

        if (count($this->errors) > 0) {
            $this->logValidationErrors();
        }

        return $allMetadata;
    }

    /**
     * @param EntityMetadata[] $allMetadata
     */
    private function validateEntityMetadata(EntityMetadata $metadata, array $allMetadata): void
    {
        $className = $metadata->getClassName();

        $this->validatePrimaryKey($className, $metadata);
        $this->validateTableName($className, $metadata);
        $this->validateFields($className, $metadata);
        $this->validateIndexes($className, $metadata);
        $this->validateUniqueConstraints($className, $metadata);
        $this->validateRelations($className, $metadata, $allMetadata);
    }

    private function validatePrimaryKey(string $className, EntityMetadata $metadata): void
    {
        if ($metadata->getPrimaryKey() === null) {
            $this->addError($className, 'Entity is missing a primary key');
        }
    }

    private function validateTableName(string $className, EntityMetadata $metadata): void
    {
        if ($metadata->getTableName() === null || trim($metadata->getTableName()) === '') {
            $this->addError($className, 'Entity is missing a table name');
        }
    }

    private function validateFields(string $className, EntityMetadata $metadata): void
    {
        foreach ($metadata->getFields() as $field) {
            if ($field->getColumnName() === null || trim($field->getColumnName()) === '') {
                $this->addError($className, sprintf(
                    'Field "%s" is missing a column name',
                    $field->getPropertyName(),
                ));
            }

            if ($field->isPrimaryKey() && $field->getGeneratedFieldMetadata() !== null) {
                $generator = $field->getGeneratedFieldMetadata()->getGeneratorType();

                if ($generator === null) {
                    $this->addError($className, sprintf(
                        'Primary key field "%s" has GeneratedValue attribute but no generator strategy specified',
                        $field->getPropertyName(),
                    ));
                }
            }
        }
    }

    private function validateIndexes(string $className, EntityMetadata $metadata): void
    {
        foreach ($metadata->getIndexes() as $index) {
            if (count($index->getColumns()) === 0) {
                $this->addError($className, sprintf(
                    'Index "%s" does not contain any columns',
                    $index->getName(),
                ));
            }

            foreach ($index->getColumns() as $column) {
                if ($metadata->getFieldByColumnName($column) === null) {
                    $this->addError($className, sprintf(
                        'Index "%s" references a non-existent column "%s"',
                        $index->getName(),
                        $column,
                    ));
                }
            }
        }
    }

    private function validateUniqueConstraints(string $className, EntityMetadata $metadata): void
    {
        foreach ($metadata->getUniqueConstraints() as $constraint) {
            if (count($constraint->getColumns()) === 0) {
                $this->addError($className, sprintf(
                    'Unique constraint "%s" does not contain any columns',
                    $constraint->getName(),
                ));
            }

            foreach ($constraint->getColumns() as $column) {
                if ($metadata->getFieldByColumnName($column) === null) {
                    $this->addError($className, sprintf(
                        'Unique constraint "%s" references a non-existent column "%s"',
                        $constraint->getName(),
                        $column,
                    ));
                }
            }
        }
    }

    /**
     * @param EntityMetadata[] $allMetadata
     */
    private function validateRelations(string $className, EntityMetadata $metadata, array $allMetadata): void
    {
        foreach ($metadata->getRelations() as $relation) {
            $targetEntity = $relation->getTargetEntity();
            $relationType = $relation->getType();
            $fieldName = $relation->getFieldName();

            if (!isset($allMetadata[$targetEntity])) {
                $this->addError($className, sprintf(
                    'Relation "%s" references a non-existent entity "%s"',
                    $fieldName,
                    $targetEntity,
                ));
                continue;
            }

            if ($relation->getJoinColumns() !== null) {
                foreach ($relation->getJoinColumns() as $joinColumn) {
                    if ($joinColumn->getName() === null || trim($joinColumn->getName()) === '') {
                        $this->addError($className, sprintf(
                            'Join column in relation "%s" is missing a name',
                            $fieldName,
                        ));
                    }

                    if ($joinColumn->getReferencedColumnName() === null || trim($joinColumn->getReferencedColumnName()) === '') {
                        $this->addError($className, sprintf(
                            'Join column "%s" in relation "%s" is missing a referenced column name',
                            $joinColumn->getName() ?? 'unnamed',
                            $fieldName,
                        ));
                    }
                }
            }

            if ($relationType->name === 'ManyToMany' && $relation->getMappedBy() === null) {
                $joinTable = $relation->getJoinTable();

                if ($joinTable === null) {
                    $this->addError($className, sprintf(
                        'ManyToMany relation "%s" is missing a join table definition',
                        $fieldName,
                    ));
                } else {
                    if ($joinTable->getName() === null || trim($joinTable->getName()) === '') {
                        $this->addError($className, sprintf(
                            'Join table in ManyToMany relation "%s" is missing a name',
                            $fieldName,
                        ));
                    }

                    if (empty($joinTable->getJoinColumns())) {
                        $this->addError($className, sprintf(
                            'Join table in ManyToMany relation "%s" is missing join columns',
                            $fieldName,
                        ));
                    }

                    if (empty($joinTable->getInverseJoinColumns())) {
                        $this->addError($className, sprintf(
                            'Join table in ManyToMany relation "%s" is missing inverse join columns',
                            $fieldName,
                        ));
                    }
                }
            }

            if ($relation->getInversedBy() !== null) {
                $targetMetadata = $allMetadata[$targetEntity];
                $inverseField = $relation->getInversedBy();
                $inverseRelation = $targetMetadata->getRelationByFieldName($inverseField);

                if ($inverseRelation === null) {
                    $this->addError($className, sprintf(
                        'Relation "%s" references a non-existent inverse field "%s" in entity "%s"',
                        $relation->getFieldName(),
                        $inverseField,
                        $targetEntity,
                    ));
                } elseif ($inverseRelation->getMappedBy() === null) {
                    $this->addError($className, sprintf(
                        'Relation "%s" has inversedBy="%s", but the inverse relation does not have mappedBy',
                        $relation->getFieldName(),
                        $inverseField,
                    ));
                } elseif ($inverseRelation->getMappedBy() !== $relation->getFieldName()) {
                    $this->addError($className, sprintf(
                        'Relation "%s" has inversedBy="%s", but the inverse relation has mappedBy="%s"',
                        $relation->getFieldName(),
                        $inverseField,
                        $inverseRelation->getMappedBy(),
                    ));
                }
            }

            if ($relation->getMappedBy() !== null) {
                $targetMetadata = $allMetadata[$targetEntity];
                $mappedField = $relation->getMappedBy();
                $mappedRelation = $targetMetadata->getRelationByFieldName($mappedField);

                if ($mappedRelation === null) {
                    $this->addError($className, sprintf(
                        'Relation "%s" references a non-existent field mappedBy="%s" in entity "%s"',
                        $relation->getFieldName(),
                        $mappedField,
                        $targetEntity,
                    ));
                } elseif ($mappedRelation->getInversedBy() === null) {
                    $this->addError($className, sprintf(
                        'Relation "%s" has mappedBy="%s", but the inverse relation does not have inversedBy',
                        $relation->getFieldName(),
                        $mappedField,
                    ));
                } elseif ($mappedRelation->getInversedBy() !== $relation->getFieldName()) {
                    $this->addError($className, sprintf(
                        'Relation "%s" has mappedBy="%s", but the inverse relation has inversedBy="%s"',
                        $relation->getFieldName(),
                        $mappedField,
                        $mappedRelation->getInversedBy(),
                    ));
                }
            }
        }
    }

    private function addError(string $className, string $message): void
    {
        if (!isset($this->errors[$className])) {
            $this->errors[$className] = [];
        }

        $this->errors[$className][] = $message;
    }

    private function logValidationErrors(): void
    {
        echo "\n\n=== METADATA VALIDATION ERRORS ===\n\n";

        foreach ($this->errors as $className => $errors) {
            echo "Entity: {$className}\n";

            foreach ($errors as $error) {
                echo "- {$error}\n";
            }

            echo "\n";
        }

        echo "=== END VALIDATION ERRORS ===\n\n";
    }
}
