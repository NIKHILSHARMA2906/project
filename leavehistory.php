<?php
session_start();
error_reporting(0);
include('includes/config.php');
include('includes/functions.php');
if(strlen($_SESSION['emplogin'])==0)
    {   
header('location:index.php');
}
else{
    $todayDate = mysqli_result(mysqli_query($GLOBALS['conn'],"SELECT DATE_FORMAT(CURDATE(), '%M %d, %Y')"),0,0);

 ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        
        <!-- Title -->
        <title>Employee | Leave History</title>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
        <meta charset="UTF-8">
        <meta name="description" content="Responsive Admin Dashboard Template" />
        <meta name="keywords" content="admin,dashboard" />
        <meta name="author" content="Steelcoders" />
        
        <!-- Styles -->
        <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css"/>
        <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link href="assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet">
        <link href="assets/plugins/datatables/css/jquery.dataTables.min.css" rel="stylesheet">

            
        <!-- Theme Styles -->
        <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
        <link href="assets/css/custom.css" rel="stylesheet" type="text/css"/>
<style>
        .errorWrap {
    padding: 10px;
    margin: 0 0 20px 0;
    background: #fff;
    border-left: 4px solid #dd3d36;
    -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
}
.succWrap{
    padding: 10px;
    margin: 0 0 20px 0;
    background: #fff;
    border-left: 4px solid #5cb85c;
    -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    
    
}

        </style>
    </head>
    <body>
       <?php include('includes/header.php');?>
            
       <?php include('includes/sidebar.php');?>
            <main class="mn-inner">
                <div class="row">
                    <div class="col s12 ">
                        <div class="page-title">Today's Date:  <?php echo $todayDate; ?></div>
                    </div>
                </div>
                <div class="row no-m-t no-m-b">
                    <div class="col s12 m12 l4">
                        <div class="card stats-card">
                            <div class="card-content">
                                <span class="card-title">Casual Leaves</span>
                                <span class="stats-counter">
                                    <?php
                                        $casualLeavesAllowed        = $_SESSION['allowedleaves']['casual']['allowed'];                                        
                                        $casualLeavesAvailed        = getLeavesAvailed('casual');
                                        $casualLeavesPending        = $casualLeavesAllowed - $casualLeavesAvailed;
                                    ?>
                                    <span class="counter counter-leave counter-green">Availed:  <?php echo $casualLeavesAvailed; ?></span><br/>
                                    <span class="counter counter-leave counter-blue">Balance: <?php echo $casualLeavesPending ; ?></span>
                                </span>
                            </div>
                            <div class="progress stats-card-progress">
                                <?php
                                     $width = ($casualLeavesAvailed/$casualLeavesAllowed)*100;
                                ?>
                                <div class="determinate" style="width: <?php echo $width; ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col s12 m12 l4">
                        <div class="card stats-card">
                            <div class="card-content">
                                <span class="card-title">P-Leave</span>
                                <span class="stats-counter">            
                                    <?php
                                        $pLeavesAllowed        = $_SESSION['allowedleaves']['pleave']['allowed'];                                        
                                        $pLeavesAvailed        = getLeavesAvailed('pleave');
                                        $pLeavesPending        = $pLeavesAllowed - $pLeavesAvailed;
                                    ?>                        
                                    <span class="counter counter-leave counter-green">Availed:  <?php echo $pLeavesAvailed; ?></span><br/>
                                    <span class="counter counter-leave counter-blue">Balance:  <?php echo $pLeavesPending; ?></span>
                                </span>
                            </div>
                            <div class="progress stats-card-progress">
                                <?php
                                     $width = ($pLeavesAvailed/$pLeavesAllowed)*100;
                                ?>
                                <div class="determinate" style="width: <?php echo $width; ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col s12 m12 l4">
                        <div class="card stats-card">
                            <div class="card-content">
                                <span class="card-title">Medical Leave</span>
                                <span class="stats-counter">
                                    <?php
                                        $medicalLeavesAllowed        = $_SESSION['allowedleaves']['medical']['allowed'];                                        
                                        $medicalLeavesAvailed        = getLeavesAvailed('medical');
                                        $medicalLeavesPending        = $medicalLeavesAllowed - $medicalLeavesAvailed;
                                    ?>
                                    <span class="counter counter-leave counter-green">Availed:  <?php echo $medicalLeavesAvailed; ?></span><br/>
                                    <span class="counter counter-leave counter-blue">Balance: <?php echo $medicalLeavesPending; ?></span>
                                </span>
                            </div>
                            <div class="progress stats-card-progress">
                                <?php
                                     $width = ($medicalLeavesAvailed/$medicalLeavesAllowed)*100;
                                ?>
                                <div class="determinate" style="width: <?php echo $width; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col s12 m12 l12">
                        <div class="card">
                            <div class="card-content">
                                <span class="card-title">Leave History</span>
                                <?php if($msg){?><div class="succWrap"><strong>SUCCESS</strong> : <?php echo htmlentities($msg); ?> </div><?php }?>
                                <table id="example" class="display responsive-table ">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th width="120">Leave Type</th>
                                            <th>From</th>
                                            <th>To</th>
                                             <th width="120">Posting Date</th>
                                             <th width="120">Days</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                 
                                    <tbody>
<?php 
$eid=$_SESSION['eid'];
$sql = "SELECT tblleaves.id as lid ,LeaveType, DATE_FORMAT(ToDate,'%d %M, %Y')AS ToDate,DATE_FORMAT(FromDate,'%d %M, %Y') AS FromDate,Description, DATE_FORMAT(PostingDate,'%d %M, %Y') AS PostingDate, AdminRemarkDate,AdminRemark,Status,nod from tblleaves where empid=:eid";
$query = $dbh -> prepare($sql);
$query->bindParam(':eid',$eid,PDO::PARAM_STR);
$query->execute();
$results=$query->fetchAll(PDO::FETCH_OBJ);
$cnt=1;
if($query->rowCount() > 0)
{
foreach($results as $result)
{               ?>  
                                        <tr>
                                            <td> <?php echo htmlentities($cnt);?></td>
                                            <td>
                                                <?php 
                                                $leaveTypeId = $result->LeaveType;
                                                $leaveType   = getLeaveType($leaveTypeId);
                                                echo htmlentities($leaveType);
                                                ?>
                                            </td>
                                            <td><?php echo htmlentities($result->FromDate);?></td>
                                            <td><?php echo htmlentities($result->ToDate);?></td>
                                            <td><?php echo htmlentities($result->PostingDate);?></td>
                                            <td><?php echo htmlentities($result->nod);?></td>
                                           
                                                                                 <td><?php $stats=$result->Status;
if($stats==1){
                                             ?>
                                                 <span style="color: green">Approved</span>
                                                 <?php } if($stats==2)  { ?>
                                                <span style="color: red">Not Approved</span>
                                                 <?php } if($stats==0)  { ?>
 <span style="color: blue">waiting for approval</span>
 <?php } ?>

                                             </td>
                                             <td>
          <a href="leave-details.php?leaveid=<?php echo htmlentities($result->lid);?>" class="waves-effect waves-light center-align btn orange m-b-xs"  > View Details</a></td>
                                        </tr>
                                         <?php $cnt++;} }?>
                                    </tbody>
                                </table>
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
        <script src="assets/plugins/datatables/js/jquery.dataTables.min.js"></script>
        <script src="assets/js/alpha.min.js"></script>
        <script src="assets/js/pages/table-data.js"></script>
        
    </body>
</html>
<?php } ?>