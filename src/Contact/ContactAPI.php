<?php

namespace jamesiarmes\PEWS\Contact;

use jamesiarmes\PEWS\API\Type\ContactItemType;
use jamesiarmes\PEWS\API\Type;
use jamesiarmes\PEWS\API;
use jamesiarmes\PEWS\API\Enumeration;
use DateTime;

/**
 * An API end point for Contact items
 *
 * Class API
 * @package jamesiarmes\PEWS\Contact
 */
class ContactAPI extends API
{
    /**
     * @var Type\FolderIdType
     */
    protected $folderId;

    /**
     * Pick a Contact Folder based on it's name
     *
     * @param string|null $displayName
     * @return $this
     */
    public function pickContactsFolder($displayName = null)
    {
        if ($displayName == 'default.contact' || $displayName == null) {
            $folder = $this->getFolderByDistinguishedId('contacts');
        } else {
            $folder = $this->getFolderByDisplayName($displayName, 'contact');
        }

        $this->folderId = $folder->getFolderId();

        return $this;
    }

    /**
     * @return Type\FolderIdType
     */
    public function getFolderId()
    {
        if ($this->folderId === null) {
            $this->pickContactsFolder();
        }

        return $this->folderId;
    }

    /**
     * Create one or more contact items
     *
     * @param $items ContactItemType[]|ContactItemType|Array or more contact items to create
     * @param $options array Options to merge in to the request
     * @return Type\ItemIdType[]
     */
    public function createContactItems($items, $options = array())
    {
        //If the item passed in is an object, or if it's an associative]
        // array waiting to be an object, let's put it in to an array
        if (!is_array($items) || Type::arrayIsAssoc($items)) {
            $items = array($items);
        }

        $item = array('ContactItem' => $items);
        $defaultOptions = array(
            'SendMeetingInvitations' => Enumeration\ContactItemCreateOrDeleteOperationType::SEND_TO_NONE,
            'SavedItemFolderId' => array(
                'FolderId' => $this->getFolderId()->toXmlObject()
            )
        );

        $options = array_replace_recursive($defaultOptions, $options);

        $items = $this->createItems($item, $options);

        if (!is_array($items)) {
            $items = array($items);
        }

        return $items;
    }

    /**
     * @param $id
     * @param $changeKey
     * @return Type\ContactItemType
     */
    public function getContactItem($id, $changeKey)
    {
        return $this->getItem(['Id' => $id, 'ChangeKey' => $changeKey]);
    }

    /**
     * Updates a contact item with changes
     *
     * @param $itemId Type\ItemIdType
     * @param $changes
     * @return Type\ContactItemType[]
     */
    public function updateContactItem(Type\ItemIdType $itemId, $changes)
    {
        //Create the request
        $request = array(
            'ItemChange' => array(
                'ItemId' => $itemId->toArray(),
                'Updates' => array(
                    'SetItemField' => $this->buildUpdateItemChanges('ContactItem', 'contact', $changes)
                )
            )
        );

        //$options = array(
            //'SendMeetingInvitationsOrCancellations' => 'SendToNone'
        //);

        $items = $this->updateItems($request, $options)->getContactItem();

        if (!is_array($items)) {
            $items = array($items);
        }

        return $items;
    }

    /**
     * @param $itemId Type\ItemIdType
     * @return bool
     */
    public function deleteContactItem(Type\ItemIdType $itemId)
    {
        return $this->deleteItems($itemId);
    }

}
