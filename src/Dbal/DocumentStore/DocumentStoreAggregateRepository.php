<?php

namespace Ecotone\Dbal\DocumentStore;

use Ecotone\Messaging\Store\Document\DocumentNotFound;
use Ecotone\Messaging\Store\Document\DocumentStore;
use Ecotone\Modelling\StandardRepository;

final class DocumentStoreAggregateRepository implements StandardRepository
{
    private const COLLECTION_NAME = "aggregates_";

    public function __construct(private DocumentStore $documentStore) {}

    public function canHandle(string $aggregateClassName): bool
    {
        return true;
    }

    public function findBy(string $aggregateClassName, array $identifiers): ?object
    {
        $aggregateId = array_pop($identifiers);

        try {
            return $this->documentStore->getDocument($this->getCollectionName($aggregateClassName), $aggregateId);
        }catch (DocumentNotFound) {
            return null;
        }
    }

    public function save(array $identifiers, object $aggregate, array $metadata, ?int $versionBeforeHandling): void
    {
        $aggregateId = array_pop($identifiers);

        $this->documentStore->upsertDocument($this->getCollectionName($aggregate::class), $aggregateId, $aggregate);
    }

    private function getCollectionName(string $aggregateClassName): string
    {
        return self::COLLECTION_NAME . $aggregateClassName;
    }
}