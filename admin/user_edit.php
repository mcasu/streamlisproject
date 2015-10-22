<?php 
include("../check_login.php"); 
include(getenv("DOCUMENT_ROOT") . "/include/check_role_admin.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">

<body>

<div class="container-fluid">
    <div class="panel panel-primary">
        <div class="panel-body">
            <form role="form" id="formChangeUser" action='<?php echo $utils->GetSelfScript(); ?>' method='post' accept-charset='UTF-8'>
                <fieldset>
                <div class="form-group">
                    <div class="control-group">
                        <!-- CAMPO NOME COMPLETO -->
                        <label for='name' >Nome utente completo:</label><br/>
                        <div class="controls">
                            <input type="text" class="form-control" placeholder="Nome utente completo" name='name' id='name' value='<?php echo $utils->SafeDisplay('name') ?>' maxlength="128" /><br/>
                        </div>
                    </div>
                    <div class="control-group">
                        <!-- CAMPO INDIRIZZO EMAIL -->
                        <label for='email' >Indirizzo email:</label><br/>
                        <div class="controls">
                            <input type="email" class="form-control" placeholder="Indirizzo Email" name='email' id='email' value='<?php echo $utils->SafeDisplay('email') ?>' maxlength="128" /><br/>
                        </div>
                    </div>
                    <div class="control-group">
                        <!-- CAMPO USERNAME -->
                        <label for='username' >Username:</label><br/>
                        <div class="controls">
                            <input type="text" class="form-control" placeholder="Username" name='username' id='username' value='<?php echo $utils->SafeDisplay('username') ?>' maxlength="128" /><br/>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <!-- CAMPO CONGREGAZIONE -->
                    <label for='groups' >Congregazione:</label><br/>
                    <select class="form-control" name="group_name" id="group_name">
                        <?php
                            try
                            {
                                /*** query the database ***/
                                $result = $dbactions->GetGroups();

                                if (!$result)
                                {
                                        error_log("No Results");
                                }

                                while($row = mysql_fetch_array($result))
                                {
                                        $group=$row['group_name'];
                                        echo '<option value="' . $group . '">' . $group . '</option>"';
                                }
                            }
                            catch(PDOException $e)
                            {
                                echo 'No Results';
                            }
                        ?>
                </select>
                <br/>
                <!-- CAMPO TIPO UTENTE -->
                <label for='roles' >Tipo di utente:</label><br/>
                <select class="form-control" name="user_role_name" id="user_role_name">
                <?php
                    try
                    {
                        /*** query the database ***/
                        $result = $dbactions->GetUserRoles();

                        if (!$result)
                        {
                                error_log("No Results");
                        }

                        while($row = mysql_fetch_array($result))
                        {
                                $user_role_name=$row['user_role_name'];
                                echo '<option value="' . $user_role_name . '">' . $user_role_name . '</option>"';
                        }
                    }
                    catch(PDOException $e)
                    {
                        echo 'No Results';
                    }
                ?>
                </select>
                </div>
                <br/>
                </fieldset>
            </form>
        </div>
</div>

<script type='text/javascript'>
    
jQuery(document).ready(function ()
{
    $("#name").val($('#divUserEdit').data('name'));
    $("#email").val($('#divUserEdit').data('email'));
    $("#username").val($('#divUserEdit').data('username'));
    
    $('#group_name option[value="' + $('#divUserEdit').data('group') + '"]').prop('selected', true);
    $('#user_role_name option[value="' + $('#divUserEdit').data('role').toString().toLowerCase() + '"]').prop('selected', true);
    
    var options = {};
    options.common =
    {
	minChar: 8,
	bootstrap3: true,
	usernameField: "#username"
    };

    options.rules =
    {
	activated: {
	    wordTwoCharacterClasses: true,
	    wordRepetitions: true,
	    wordSimilarToUsername: true,
	    wordOneSpecialChar: true
	}
    };
    
    options.ui =
    {
	showErrors: true,
	errorMessages:
	{
	    wordLength: "La password è troppo corta",
	    wordNotEmail: "Non puoi usare un indirizzo email come password",
	    wordSimilarToUsername: "La password non può contenere il tuo username",
	    wordTwoCharacterClasses: "Use different character classes",
	    wordRepetitions: "Troppi caratteri ripetuti",
	    wordSequences: "Non puoi usare 3 caratteri successivi. (Es. 'abc' o '123')"
	}
	
    };
    
    $('#formChangeUser').validate(
    {
	rules: {
	    name: {
		required: true,
		minlength: 4
	    },
	    email: {
		required: true,
		email: true
	    },
	    username: {
		required: true,
		minlength: 6
	    }
	},
	
	highlight: function(element)
	{
	    $(element).closest('.control-group').removeClass('success').removeClass('has-success').addClass('error');
	},
	
	success: function(element)
	{
	    element.addClass('valid').closest('.control-group').removeClass('error').addClass('success').addClass('has-success');
	}
    });
});

</script>

</body>
</html>
