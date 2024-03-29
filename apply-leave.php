<?php
session_start();
error_reporting(0);
include('includes/config.php');
include('includes/functions.php');
$todayDate = mysqli_result(mysqli_query($GLOBALS['conn'], "SELECT DATE_FORMAT(CURDATE(), '%M %d, %Y')"), 0, 0);
$alertMsg="";
$error="";
$msg="";
$_SESSION['reposted'] = randomGenerator(10);


if (strlen($_SESSION['emplogin']) == 0) {
    header('location:index.php');
}
$_reposted = isset($_POST['reposted']) ? trim($_POST['reposted']) : '';

$casualLeavesAllowed = $_SESSION['allowedleaves']['casual']['allowed'];
$casualLeavesAvailed = getLeavesAvailed('casual');
$casualLeavesPending = $casualLeavesAllowed - $casualLeavesAvailed;

if ($casualLeavesPending <= 0) {
    $alertMsg = '<div class="warningWrap"> Your casual leaves have been exhausted. Please reach out to the administrator for further assistance </div>';
}

$pLeavesAllowed = $_SESSION['allowedleaves']['pleave']['allowed'];
$pLeavesAvailed = getLeavesAvailed('pleave');
$pLeavesPending = $pLeavesAllowed - $pLeavesAvailed;

if ($pLeavesPending <= 0) {
    $alertMsg .= '<div class="warningWrap mt-10"> Your paid leaves have been exhausted. Please reach out to the administrator for further assistance </div>';
}

$medicalLeavesAllowed = $_SESSION['allowedleaves']['medical']['allowed'];
$medicalLeavesAvailed = getLeavesAvailed('medical');
$medicalLeavesPending = $medicalLeavesAllowed - $medicalLeavesAvailed;

if ($medicalLeavesPending <= 0) {
    $alertMsg .= '<div class="warningWrap mt-10"> Your medical leaves have been exhausted. Please reach out to the administrator for further assistance </div>';
}




if (isset($_POST['apply'])) {
    $empid = $_SESSION['eid'];
    $leavetype = $_POST['leavetype'];
    $fromdate = $_POST['fromdate'];
    $todate = $_POST['todate'];
    $description = $_POST['reason'];
    $nod = $_POST['nod'];
    $doa = $_POST['doa'];
    $status = 1;
    $isread = 0;
    $ltName = getLeaveType($leavetype, 'innername');
    if ($ltName == 'firsthalf' || $ltName == 'secondhalf') {
        $nod = 0.5;
        $todate = $fromdate;
    }
    if ($fromdate > $todate) {
        $error = " <li>ToDate should be greater than FromDate </li> ";
    }
    switch ($ltName) {
        case 'casual': 
            if ($nod>2)
                { $error="<li> You cannot apply for more than two leaves.</li>";}
        case 'firsthalf':
        case 'secondhalf':
            if (!isset($casualLeavesPending)) {
                $casualLeavesAllowed = $_SESSION['allowedleaves']['casual']['allowed'];
                $casualLeavesAvailed = getLeavesAvailed('casual');
                $casualLeavesPending = $casualLeavesAllowed - $casualLeavesAvailed;
            }
            if ($casualLeavesPending < $nod) {
                $error = "<li>You have only {$casualLeavesPending} casual leaves pending.</li>";
            }
            break;
        case 'pleave':
            if (!isset($pLeavesPending)) {
                $pLeavesAllowed = $_SESSION['allowedleaves']['pleave']['allowed'];
                $pLeavesAvailed = getLeavesAvailed('pleave');
                $pLeavesPending = $pLeavesAllowed - $pLeavesAvailed;
            }
            if ($pLeavesPending < $nod) {
                $error = "<li>You have only {$pLeavesPending} paid leaves pending.</li>";
            }
            break;
        case 'medical':
            if (!isset($medicalLeavesPending)) {
                $medicalLeavesAllowed = $_SESSION['allowedleaves']['medical']['allowed'];
                $medicalLeavesAvailed = getLeavesAvailed('medical');
                $medicalLeavesPending = $medicalLeavesAllowed - $medicalLeavesAvailed;
            }
            if ($medicalLeavesPending < $nod) {
                $error = "<li>You have only {$medicalLeavesPending} medical leaves pending.</li>";
            }
            break;
    }
    if (empty($error)) {
        $sql = "INSERT INTO tblleaves(LeaveType,ToDate,FromDate,Description,Status,IsRead,empid, nod, PostingDate,session) VALUES(:leavetype,:todate,:fromdate,:description,:status,:isread,:empid,:nod, NOW(), :sess)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':leavetype', $leavetype, PDO::PARAM_STR);
        $query->bindParam(':fromdate', $fromdate, PDO::PARAM_STR);
        $query->bindParam(':todate', $todate, PDO::PARAM_STR);
        $query->bindParam(':description', $description, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->bindParam(':isread', $isread, PDO::PARAM_STR);
        $query->bindParam(':empid', $empid, PDO::PARAM_STR);
        $query->bindParam(':nod', $nod, PDO::PARAM_STR);
        $query->bindParam(':sess', $currentSession, PDO::PARAM_STR);
        //$query->bindParam(':doa',$doa,PDO::PARAM_STR);
        $query->execute();
        $lastInsertId = $dbh->lastInsertId();
        if ($lastInsertId) {
            $msg = "   Your leave has been submitted successfully.";

            $leavetype =
                $fromdate =
                $todate =
                $description =
                $nod =
                $doa = '';
        } else {
            $error = "Something went wrong. Please try again";
        }
    }

}

/* Find leaves availed */
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <!-- Title -->
    <title>Employe | Apply Leave</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta charset="UTF-8">
    <meta name="description" content="Responsive Admin Dashboard Template" />
    <meta name="keywords" content="admin,dashboard" />
    <meta name="author" content="Steelcoders" />

    <!-- Styles -->
    <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css" />
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet">
    <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/custom.css" rel="stylesheet" type="text/css" />
    <style>
        .errorWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #dd3d36;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
        }

        .succWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #5cb85c;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
        }
    </style>



</head>

<body>
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>
    <main class="mn-inner">
        <div class="row">
            <div class="col s12">
                <div class="page-title dav-page-title">
                    Apply for Leave


                </div>
                <!-- commented by me
                    <table>
                    <tr>
                        <th width="22%">Casual Leaves Pending:</th>
                        <td><?php echo (int) $casualLeavesPending; ?></td>
                        <th width="22%">Medical Leaves Pending:</th>
                        <td><?php echo (int) $medicalLeavesPending; ?></td>
                        <th width="22%">P-Leaves Pending</th>
                        <td><?php echo (int) $pLeavesPending; ?></td>
                    </tr>
                </table> -->

            </div>
            <div class="col s12 m12 l12">
                <div class="card ">

                    <div class="card-content  blue lighten-5">
                        <?php
                        $sql = "SELECT EmpId,FirstName,LastName,Department, designation from  tblemployees WHERE id={$_SESSION['eid']}";

                        $results = mysqli_query($conn, $sql);// or die(mysqli_error($conn))
                        $cnt = 1;
                        if (mysqli_num_rows($results) > 0) {
                            while ($row = mysqli_fetch_assoc($results)) {
                                $id = $row['EmpId'];
                                $name = $row['FirstName'] . ' ' . $row['LastName'];
                                $department = $row['Department'];
                                $designation = $row['designation'];
                            }
                        }
                        ?>
                        <table id="example" class="display responsive-table">
                            <tr>
                                <td width="25%" class="apply-leave-col-head">
                                    Employee ID:
                                </td>
                                <td width="25%">
                                    <strong>
                                        <?php echo $id; ?>
                                    </strong>
                                </td>
                                <td width="25%" class="apply-leave-col-head">
                                    Name :
                                </td>
                                <td width="25%">
                                    <strong>
                                        <?php echo $name; ?>
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <td width="25%" class="apply-leave-col-head">
                                    Department:
                                </td>
                                <td width="25%">
                                    <strong>
                                        <?php echo (empty($department) ? 'N/A' : $department); ?>
                                    </strong>
                                </td>
                                <td width="25%" class="apply-leave-col-head">
                                    Designation:
                                </td>
                                <td width="25%">
                                    <strong>
                                        <?php echo empty($designation) ? 'N/A' : $designation; ?>
                                    </strong>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <form id="example-form" method="post" name="addemp">
                        <div>
                            <section>
                                <div class="wizard-content">
                                    <div class="row">
                                        <div class="col m12">

                                            <div class="row">
                                                <?php echo $alertMsg; ?>
                                                <?php if ($error) { ?>
                                                    <div class="errorWrap"><strong>ERROR </strong>:
                                                        <?php echo $error; ?>
                                                    </div>
                                                <?php } else if ($msg) { ?>
                                                        <div class="succWrap"><strong>SUCCESS</strong>:
                                                        <?php echo $msg; ?>
                                                        </div>
                                                <?php } ?>


                                                <div class="input-field col  s12">
                                                    <select name="leavetype" id="leavetype" autocomplete="off" required>

                                                        <option value="">Select leave type...</option>
                                                        <?php $sql = "SELECT  id,LeaveType, inner_name from tblleavetype";
                                                        $query = $dbh->prepare($sql);
                                                        $query->execute();
                                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                                        $cnt = 1;
                                                        if ($query->rowCount() > 0) {
                                                            foreach ($results as $result) {
                                                                $selected = $leavetype == $result->id ? 'selected="selected"' : '';
                                                                ?>
                                                                <option <?php echo $selected; ?>
                                                                    value="<?php echo $result->id; ?>"
                                                                    data-name="<?php echo $result->inner_name; ?>">
                                                                    <?php echo htmlentities($result->LeaveType); ?>
                                                                </option>
                                                                <?php
                                                            }
                                                        } ?>
                                                    </select>
                                                </div>

                                                <div class="input-field col m12 s12">
                                                    <input id="doa" name="doa" type="text" autocomplete="off"
                                                        value="<?php echo $todayDate; ?>" readonly="readonly">
                                                    <label for="doa" class="active">Date of Application</label>
                                                </div>
                                                <div class="input-field col m6 s12">
                                                    <input id="fromdate" name="fromdate" type="date" class="datepicker"
                                                        autocomplete="off" value="<?php echo $fromdate; ?>"
                                                        onchange="javascript:diff();" required>
                                                    <label for="fromdate" class="active">From Date</label>
                                                </div>
                                                <div class="input-field col m6 s12">
                                                    <input id="todate" name="todate" type="date" class="datepicker"
                                                        autocomplete="off" value="<?php echo $todate; ?>"
                                                        onchange="javascript:diff();" required>
                                                    <label for="todate" class="active">To Date</label>
                                                </div>
                                                <div class="input-field col m12 s12">
                                                    <input id="nod" name="nod" type="number" value="<?php echo $nod; ?>"
                                                        autocomplete="off" readonly />
                                                    <label for="nod"class="active">Number of Days</label>
                                                </div>
                                                <div class="input-field col m12 s12">
                                                    <textarea id="textarea1" name="reason" class="materialize-textarea"
                                                        length="500" required><?php echo $description; ?></textarea>
                                                    <label for="textarea1" class="active">Reason</label>
                                                </div>
                                                <div class="input-field col m12 s12">
                                                    <input id="attachment" name="attachment" type="file"
                                                        autocomplete="off">
                                                </div>
                                            </div>
                                            <input type="hidden" aria-hidden="true" name="reposted"
                                                value="<?php echo $_SESSION['reposted']; ?>" />
                                            <button type="submit" name="apply" id="apply"
                                                class="waves-effect waves-light btn orange m-b-xs ml-10 mt-20"
                                                >Apply</button>

                                        </div>
                                    </div>
                            </section>


                            </section>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
    </main>
    </div>
    <div class="left-sidebar-hover"></div>

    <!-- Javascripts -->
    <script src="assets/plugins/jquery/jquery-2.2.0.min.js"></script>
    <script src="assets/plugins/materialize/js/materialize.min.js"></script>
    <script src="assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
    <script src="assets/plugins/jquery-blockui/jquery.blockui.js"></script>
    <script src="assets/js/alpha.min.js"></script>
    <script>
        //jQuery('d')
        function diff() {
            
            var start = new Date(document.getElementById("fromdate").value);
            var end = new Date(document.getElementById("todate").value);
            var leaveType = jQuery('#leavetype').find(':selected').data('name');

            if (leaveType == 'firsthalf' || leaveType == 'secondhalf') {
                jQuery('#nod').val(parseFloat(0.5));
                document.getElementById("todate").value = document.getElementById("fromdate").value;

            } else {
                var diff = end - start;
                var days = (diff / (1000 * 60 * 60 * 24));
                if (days >= 0) {
                    document.getElementById("nod").value = days + 1;
                }
                else {
                    document.getElementById("nod").value = days - 1;
                }
            }
        }

        jQuery(function () {
            var dtToday = new Date();

            var month = dtToday.getMonth() + 1;
            var day = dtToday.getDate();
            var year = dtToday.getFullYear();
            if (month < 10)
                month = '0' + month.toString();
            if (day < 10)
                day = '0' + day.toString();

            var maxDate = year + '-' + month + '-' + day;
            jQuery('#fromdate').attr('min', maxDate);

        });



        function checkTodate() {
            var fromdate = new Date(document.getElementById("fromdate").value);
            var todate = new Date(document.getElementById("todate").value);

            if (todate < fromdate) {
                alert("To date should be greater than or equal to From date.");
                document.getElementById("todate").value = "";
                return false;
            }

            return true;
        }
//         function checkLeaveApplication() {
//             var leaveType = document.getElementById('leavetype').value;
//             var numberOfDays = parseInt(document.getElementById('nod').value);
// alert("yes");
// console.log('Leave Type: ' + leaveType + ', Number of Days: ' + numberOfDays);
//             if (leaveType === 'casual' && numberOfDays > 2) {
//                 alert('You cannot apply for more than two days of casual leave at a time.');
//             } else {
//                 // Submit the form or perform other actions here
//                 // For example: submitForm();
//             }
//         }

        // Example usage


    </script>



    </script>
    <script src="assets/js/pages/form_elements.js"></script>
    <script src="assets/js/pages/form-input-mask.js"></script>
    <script src="assets/plugins/jquery-inputmask/jquery.inputmask.bundle.js"></script>
    <span></span>

</body>

</html>