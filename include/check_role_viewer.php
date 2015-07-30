<?php

/* 
 * Copyright (C) 2015 marco.casu
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

$user_role = $mainactions->GetSessionUserRole();

// Se il ruolo NON è Viewer
if (empty($user_role) || $user_role != "2")
{
    switch ($user_role) 
    {
        case "1": // admin
            $utils->RedirectToURL("/admin/dashboard.php");
            break;
        case "3": // publisher
            $utils->RedirectToURL("/publisher/dashboard.php");
            break;
        default:
            // Access forbidden:
            header('HTTP/1.1 403 Forbidden');
            // Set our response code
            http_response_code(403);
            echo "<h1>403 Forbidden - Url non valida.</h1><br/>";
            exit;
    }
}

