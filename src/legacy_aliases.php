<?php

/**
 * Backwards capability aliases
 */

use Schemantic\Attribute\Chrono;
use Schemantic\Attribute\Group;
use Schemantic\Attribute\Alias;

// these classes were cataloged into sub-namespaces
class_alias(Chrono\DateTimeAttributeInterface::class, 'Schemantic\Attribute\DateTimeAttributeInterface');
class_alias(Chrono\DateTimeFormat::class, 'Schemantic\Attribute\DateTimeFormat');
class_alias(Chrono\Timestamp::class, 'Schemantic\Attribute\Timestamp');
class_alias(Group\GroupingAttributeInterface::class, 'Schemantic\Attribute\GroupingAttributeInterface');
class_alias(Group\SingleAttributeInterface::class, 'Schemantic\Attribute\SingleAttributeInterface');
class_alias(Group\RepetitiveAttributeInterface::class, 'Schemantic\Attribute\RepetitiveAttributeInterface');
class_alias(Group\Group::class, 'Schemantic\Attribute\Group');
class_alias(Alias\Alias::class, 'Schemantic\Attribute\Alias');
