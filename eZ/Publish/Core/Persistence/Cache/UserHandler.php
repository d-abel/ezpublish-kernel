<?php

/**
 * File containing a User Handler impl.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\User\Handler as UserHandlerInterface;
use eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\RoleCreateStruct;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Policy;

/**
 * Cache handler for user module.
 */
class UserHandler extends AbstractHandler implements UserHandlerInterface
{
    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::create
     */
    public function create(User $user)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $user));
        $return = $this->persistenceHandler->userHandler()->create($user);

        // Clear corresponding content cache as creation of the User changes it's external data
        $this->cache->invalidateTags(['content-fields-'.$user->id]);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::load
     */
    public function load($userId)
    {
        $cacheItem = $this->cache->getItem("ez-user-${userId}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('user' => $userId));
        $user = $this->persistenceHandler->userHandler()->load($userId);

        $cacheItem->set($user);
        $cacheItem->tag(['content-'.$user->id, 'user-'.$user->id]);
        $this->cache->save($cacheItem);

        return $user;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadByLogin
     */
    public function loadByLogin($login)
    {
        $cacheItem = $this->cache->getItem("ez-user-login-${login}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('user' => $login));
        $user = $this->persistenceHandler->userHandler()->loadByLogin($login);

        $cacheItem->set($user);
        $cacheItem->tag(['content-'.$user->id, 'user-'.$user->id]);
        $this->cache->save($cacheItem);

        return $user;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadByEmail
     */
    public function loadByEmail($email)
    {
        $cacheItem = $this->cache->getItem('ez-user-email-'.str_replace('@', '§', $email));
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('email' => $email));
        $users = $this->persistenceHandler->userHandler()->loadByEmail($email);

        $cacheItem->set($users);
        $cacheTags = [];
        foreach ($users as $user) {
            $cacheTags[] = 'content-'.$user->id;
            $cacheTags[] = 'user-'.$user->id;
        }
        $cacheItem->tag($cacheTags);
        $this->cache->save($cacheItem);

        return $users;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::update
     */
    public function update(User $user)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $user));
        $return = $this->persistenceHandler->userHandler()->update($user);

        // Clear corresponding content cache as update of the User changes it's external data
        $this->cache->invalidateTags(['content-fields-'.$user->id, 'user-'.$user->id]);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::delete
     */
    public function delete($userId)
    {
        $this->logger->logCall(__METHOD__, array('user' => $userId));
        $return = $this->persistenceHandler->userHandler()->delete($userId);

        // user id == content id == group id
        $this->cache->invalidateTags(['content-fields-'.$userId, 'user-'.$userId]);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::createRole
     */
    public function createRole(RoleCreateStruct $createStruct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $createStruct));

        return $this->persistenceHandler->userHandler()->createRole($createStruct);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::createRoleDraft
     */
    public function createRoleDraft($roleId)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleId));

        return $this->persistenceHandler->userHandler()->createRoleDraft($roleId);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRole
     */
    public function loadRole($roleId, $status = Role::STATUS_DEFINED)
    {
        if ($status !== Role::STATUS_DEFINED) {
            $this->logger->logCall(__METHOD__, array('role' => $roleId));

            return $this->persistenceHandler->userHandler()->loadRole($roleId, $status);
        }

        $cacheItem = $this->cache->getItem("ez-role-${roleId}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('role' => $roleId));
        $role = $this->persistenceHandler->userHandler()->loadRole($roleId, $status);

        $cacheItem->set($role);
        $cacheItem->tag(['role-'.$role->id]);
        $this->cache->save($cacheItem);

        return $role;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoleByIdentifier
     */
    public function loadRoleByIdentifier($identifier, $status = Role::STATUS_DEFINED)
    {
        if ($status !== Role::STATUS_DEFINED) {
            $this->logger->logCall(__METHOD__, array('role' => $identifier));

            return $this->persistenceHandler->userHandler()->loadRoleByIdentifier($identifier, $status);
        }

        $cacheItem = $this->cache->getItem("ez-role-identifier-${identifier}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('role' => $identifier));
        $role = $this->persistenceHandler->userHandler()->loadRoleByIdentifier($identifier, $status);

        $cacheItem->set($role);
        $cacheItem->tag(['role-'.$role->id]);
        $this->cache->save($cacheItem);

        return $role;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoleDraftByRoleId
     */
    public function loadRoleDraftByRoleId($roleId)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleId));

        return $this->persistenceHandler->userHandler()->loadRoleDraftByRoleId($roleId);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoles
     */
    public function loadRoles()
    {
        $this->logger->logCall(__METHOD__);

        return $this->persistenceHandler->userHandler()->loadRoles();
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoleAssignment
     */
    public function loadRoleAssignment($roleAssignmentId)
    {
        $cacheItem = $this->cache->getItem("ez-role-assignment-${roleAssignmentId}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('assignment' => $roleAssignmentId));
        $roleAssignment = $this->persistenceHandler->userHandler()->loadRoleAssignment($roleAssignmentId);

        $cacheItem->set($roleAssignment);
        $cacheItem->tag(
            [
                'role-assignment-group-list-'.$roleAssignment->contentId,
                'role-assignment-role-list-'.$roleAssignment->roleId,
                'role-assignment-'.$roleAssignmentId
            ]
        );
        $this->cache->save($cacheItem);

        return $roleAssignment;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoleAssignmentsByRoleId
     */
    public function loadRoleAssignmentsByRoleId($roleId)
    {
        $cacheItem = $this->cache->getItem("ez-role-assignment-by-role-${roleId}");
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('role' => $roleId));
        $roleAssignments = $this->persistenceHandler->userHandler()->loadRoleAssignmentsByRoleId($roleId);

        $cacheItem->set($roleAssignments);
        $cacheTags = ['role-assignment-role-list-'.$roleId];
        foreach($roleAssignments as $roleAssignment) {
            $cacheTags[] = 'role-assignment-'.$roleAssignment->id;
            $cacheTags[] = 'role-assignment-group-list-'.$roleAssignment->contentId;
        }
        $cacheItem->tag($cacheTags);
        $this->cache->save($cacheItem);

        return $roleAssignments;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadRoleAssignmentsByGroupId
     */
    public function loadRoleAssignmentsByGroupId($groupId, $inherit = false)
    {
        if ($inherit) {
            $cacheItem = $this->cache->getItem("ez-role-assignment-by-role-${groupId}-inherited");
        } else {
            $cacheItem = $this->cache->getItem("ez-role-assignment-by-role-${groupId}");
        }

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $this->logger->logCall(__METHOD__, array('group' => $groupId, 'inherit' => $inherit));
        $roleAssignments = $this->persistenceHandler->userHandler()->loadRoleAssignmentsByGroupId($groupId, $inherit);

        $cacheItem->set($roleAssignments);
        // Tag below is for empty results,  non empty it might have duplicated tags but cache will reduce those.
        $cacheTags = ['role-assignment-group-list-'.$groupId];
        foreach($roleAssignments as $roleAssignment) {
            $cacheTags[] = 'role-assignment-'.$roleAssignment->id;
            $cacheTags[] = 'role-assignment-group-list-'.$roleAssignment->contentId;
            $cacheTags[] = 'role-assignment-role-list-'.$roleAssignment->roleId;
        }
        $cacheItem->tag($cacheTags);
        $this->cache->save($cacheItem);

        return $roleAssignments;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::updateRole
     */
    public function updateRole(RoleUpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $struct));
        $this->persistenceHandler->userHandler()->updateRole($struct);

        $this->cache->invalidateTags(['role-'.$struct->id]);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::deleteRole
     */
    public function deleteRole($roleId, $status = Role::STATUS_DEFINED)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleId));
        $return = $this->persistenceHandler->userHandler()->deleteRole($roleId, $status);

        if ($status === Role::STATUS_DEFINED) {
            $this->cache->invalidateTags(['role-'.$roleId, 'role-assignment-role-list-'.$roleId]);
        }

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::publishRoleDraft
     */
    public function publishRoleDraft($roleDraftId)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleDraftId));
        $userHandler = $this->persistenceHandler->userHandler();
        $roleDraft = $userHandler->loadRole($roleDraftId, Role::STATUS_DRAFT);
        $return = $userHandler->publishRoleDraft($roleDraftId);

        // If there was a original role for the draft, then we clean cache for it
        if ($roleDraft->originalId > -1) {
            $this->cache->invalidateTags(['role-'.$roleDraft->originalId]);
        }

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::addPolicyByRoleDraft
     */
    public function addPolicyByRoleDraft($roleId, Policy $policy)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleId, 'struct' => $policy));

        return $this->persistenceHandler->userHandler()->addPolicyByRoleDraft($roleId, $policy);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::addPolicy
     */
    public function addPolicy($roleId, Policy $policy)
    {
        $this->logger->logCall(__METHOD__, array('role' => $roleId, 'struct' => $policy));
        $return = $this->persistenceHandler->userHandler()->addPolicy($roleId, $policy);

        $this->cache->invalidateTags(['role-'.$roleId]);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::updatePolicy
     */
    public function updatePolicy(Policy $policy)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $policy));
        $return = $this->persistenceHandler->userHandler()->updatePolicy($policy);

        $this->cache->invalidateTags(['policy-'.$policy->id, 'role-'.$policy->roleId]);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::deletePolicy
     */
    public function deletePolicy($policyId)
    {
        $this->logger->logCall(__METHOD__, array('policy' => $policyId));
        $this->persistenceHandler->userHandler()->deletePolicy($policyId);

        $this->cache->invalidateTags(['policy-'.$policyId]);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::loadPoliciesByUserId
     */
    public function loadPoliciesByUserId($userId)
    {
        $this->logger->logCall(__METHOD__, array('user' => $userId));

        return $this->persistenceHandler->userHandler()->loadPoliciesByUserId($userId);
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::assignRole
     */
    public function assignRole($contentId, $roleId, array $limitation = null)
    {
        $this->logger->logCall(__METHOD__, array('group' => $contentId, 'role' => $roleId, 'limitation' => $limitation));
        $return = $this->persistenceHandler->userHandler()->assignRole($contentId, $roleId, $limitation);

        $this->cache->invalidateTags(['role-assignment-group-list-'.$contentId, 'role-assignment-role-list-'.$roleId]);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::unassignRole
     */
    public function unassignRole($contentId, $roleId)
    {
        $this->logger->logCall(__METHOD__, array('group' => $contentId, 'role' => $roleId));
        $return = $this->persistenceHandler->userHandler()->unassignRole($contentId, $roleId);

        $this->cache->invalidateTags(['role-assignment-group-list-'.$contentId, 'role-assignment-role-list-'.$roleId]);

        return $return;
    }

    /**
     * @see eZ\Publish\SPI\Persistence\User\Handler::removeRoleAssignment
     */
    public function removeRoleAssignment($roleAssignmentId)
    {
        $this->logger->logCall(__METHOD__, array('assignment' => $roleAssignmentId));
        $return = $this->persistenceHandler->userHandler()->removeRoleAssignment($roleAssignmentId);

        $this->cache->invalidateTags(['role-assignment-'.$roleAssignmentId]);

        return $return;
    }
}
