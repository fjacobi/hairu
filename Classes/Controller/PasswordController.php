<?php

namespace PAGEmachine\Hairu\Controller;

/*
 * This file is part of the PAGEmachine Hairu project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 3
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller for password tasks
 */
class PasswordController extends AbstractController
{
    /**
     * Update password form view
     *
     * @return void
     */
    public function showPasswordEditFormAction()
    {
        if ($this->authenticationService->isUserAuthenticated()) {
            $user = $this->authenticationService->getAuthenticatedUser();

            $this->view->assignMultiple([
                'user' => $user,
            ]);
        }
    }

    /**
     * Initialize complete password edit
     *
     * @return void
     */
    protected function initializeCompletePasswordEditAction()
    {
        // Password repeat validation needs to be added manually here to access the password value
        $passwordRepeatArgumentValidator = $this->arguments->getArgument('passwordRepeat')->getValidator();
        $passwordsEqualValidator = $this->validatorResolver->createValidator('PAGEmachine.Hairu:EqualValidator', [
            'equalTo' => $this->request->getArgument('password'),
        ]);
        $passwordRepeatArgumentValidator->addValidator($passwordsEqualValidator);
    }

    /**
     * Complete password edit
     *
     * @param string $password       New password of the user
     * @param string $passwordRepeat Confirmation of the new password
     *
     * @return void
     * @validate $password NotEmpty
     * @validate $passwordRepeat NotEmpty
     */
    public function completePasswordEditAction($password, $passwordRepeat)
    {
        if ($this->authenticationService->isUserAuthenticated()) {
            $user = $this->authenticationService->getAuthenticatedUser();

            $user->setPassword($this->passwordService->applyTransformations($password));
            $this->frontendUserRepository->update($user);

            $this->addLocalizedFlashMessage('resetPassword.completed', [$user->getUsername()], FlashMessage::OK);

            $this->forward('showPasswordEditForm');
        }
    }
}
