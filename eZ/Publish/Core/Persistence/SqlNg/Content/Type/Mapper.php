<?php
/**
 * File containing the Mapper class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\SqlNg\Content\Type;

use eZ\Publish\SPI\Persistence;

/**
 * Mapper for Content Type Handler.
 *
 * Performs mapping of Type objects.
 */
class Mapper
{
    /**
     * Creates a Group from its create struct.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct $struct
     *
     * @todo $description is not supported by database, yet
     *
     * @return Group
     */
    public function createGroupFromCreateStruct( Persistence\Content\Type\Group\CreateStruct $struct )
    {
        $group = new Persistence\Content\Type\Group();

        $group->name = $struct->name;
        $group->description = $struct->description;
        $group->identifier = $struct->identifier;
        $group->created = $struct->created;
        $group->modified = $struct->modified;
        $group->creatorId = $struct->creatorId;
        $group->modifierId = $struct->modifierId;

        return $group;
    }

    /**
     * Extracts Group objects from the given $rows.
     *
     * @param array $rows
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Group[]
     */
    public function extractGroupsFromRows( array $rows )
    {
        $groups = array();

        foreach ( $rows as $row )
        {
            $group = new Persistence\Content\Type\Group();
            $group->id = (int)$row['id'];
            $group->created = (int)$row['created'];
            $group->creatorId = (int)$row['creator_id'];
            $group->modified = (int)$row['modified'];
            $group->modifierId = (int)$row['modifier_id'];
            $group->identifier = $row['identifier'];
            $group->name = json_decode( $row['name'], true );
            $group->description = json_decode( $row['description'], true );

            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * Extracts types and related data from the given $rows.
     *
     * @param array $rows
     *
     * @return array(Type)
     */
    public function extractTypesFromRows( array $rows )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Creates a FieldDefinition from the data in $row.
     *
     * @param array $row
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    public function extractFieldFromRow( array $row )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Maps properties from $struct to $type.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\CreateStruct $createStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    public function createTypeFromCreateStruct( Persistence\Content\Type\CreateStruct $createStruct )
    {
        $type = new Persistence\Content\Type();

        $type->name = $createStruct->name;
        $type->status = $createStruct->status;
        $type->description = $createStruct->description;
        $type->identifier = $createStruct->identifier;
        $type->created = $createStruct->created;
        $type->modified = $createStruct->modified;
        $type->creatorId = $createStruct->creatorId;
        $type->modifierId = $createStruct->modifierId;
        $type->remoteId = $createStruct->remoteId;
        $type->urlAliasSchema = $createStruct->urlAliasSchema;
        $type->nameSchema = $createStruct->nameSchema;
        $type->isContainer = $createStruct->isContainer;
        $type->initialLanguageId = $createStruct->initialLanguageId;
        $type->groupIds = $createStruct->groupIds;
        $type->fieldDefinitions = $createStruct->fieldDefinitions;
        $type->defaultAlwaysAvailable = $createStruct->defaultAlwaysAvailable;
        $type->sortField = $createStruct->sortField;
        $type->sortOrder = $createStruct->sortOrder;

        return $type;
    }

    /**
     * Creates a create struct from an existing $type.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $type
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\CreateStruct
     */
    public function createCreateStructFromType( Type $type )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Maps a FieldDefinition from the given $storageFieldDef
     *
     * @param array $row
     * @return Persistence\Content\Type\FieldDefinition
     */
    public function toFieldDefinition( array $row )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }
}
