<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public const ADMIN_ = 'pages.admin.';
    public const TRAINER = 'pages.staff.';
    public const SUPERVISOR_ = 'pages.supervisor.';

    public const SUCCESS_ = 'success';
    public const ERROR_ = 'error';
    public const AUTH_ERROR_ = 'auth-error';
    public const SUCCESS_STORE = ' has been stored successfully!';
    public const SUCCESS_UPDATE = ' has been updated successfully!';
    public const SUCCESS_DELETE = ' has been deleted successfully!';
    public const SUCCESS_REMOVE = ' has been removed successfully!';
    public const SUCCESS_ADD = ' has been Added successfully!';
    public const SUCCESS_NO_UPDATE = 'Nothing to update!';
    public const ERROR_UNKNOWN = 'Something went wrong, please try again!';
    public const ERROR_UNAUTHORIZED_ACCESS = 'You do not have permission to access this page!';
    public const ERROR_UNAUTHORIZED_ACTION = 'You are not authorized for this action!';

}
