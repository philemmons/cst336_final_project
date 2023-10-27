<?php
session_start();

if (!isset($_SESSION["status"])) {  //Check whether the admin has logged in
    $_SESSION["name"] = "Guest";
}

include 'header.html';
include 'php/sourceFinal.php';

$dbConn = getDBConnection();

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
}



?>
<script>
    function atCon(titleId, yearId, issueId) {
        $("#modalInfo").html("");
        $("#modalTitle").html("");
        $.ajax({
            type: "get", // HTTP method to use for the request
            url: "api/getComicInfo.php", // URL to which the request is sent.
            dataType: "json", // type of data expected back from the server
            data: {
                "tID": titleId, // map that is sent to the server with the request.
                "yID": yearId,
                "iID": issueId
            },
            success: function(data, status) { //callback function that is executed if the request succeeds.
                alert(data[0].creator); //ALSO THE BRACKETS in the preview indicate an object!
                //to be sure something is being sent back from the api 
                // *****  inspect element when working with js -->network: remote server --> response  --> console to examine data
                //test the api in the browser to be certain it works
                if (jQuery.isEmptyObject(data[0])) {
                    $("#modalTitle").append("<h3>Sorry!</h3>")
                    $("#modalInfo").append("At this time the creator is not scheduled at any conventions.");
                } else {
                    $("#modalTitle").append("Creator: " + data[0].creator);
                    $("#modalInfo").append("Convention: " + data[0].conName + "<br>");
                    $("#modalInfo").append("City: " + data[0].city + "<br>");
                    $("#modalInfo").append("State: " + data[0].state + "<br>");
                    $("#modalInfo").append("Attendance: " + data[0].turnOut + "<br>");
                    $("#modalInfo").append("<a href = '" + data[0].website + "\'>" + data[0].website + "</a>");
                }
            },
            complete: function(data, status) { //callback function that executes whenever the request finishes.
                alert(status);
            }
        }); //AJAX 1

    }
</script>

<?php
function dataDisplay($comic)
{
    foreach ($comic as $page) {
        echo "<tr>";
        echo "<td>" . $page['title'] . "</td><td>" . $page['creator'] . "</td><td>" . $page['issue'] . "</td><td>" . $page['publisher'] . "</td><td>" . $page['year'] . "</td>";
        echo "<td> <a data-toggle='modal' href='#myModal' onclick = 'atCon(\"" . $page['title'] . "\",\"" . $page['year'] . "\",\"" . $page['issue'] . "\")'>more</a></td>";
        echo "</tr>";
    }
}
?>

<!-- Collect the nav links, forms, and other content for toggling -->
<ul class="navbar-nav me-auto mb-2 mb-lg-0">
    <li class="nav-item">
        <a class="nav-link" href="index.php">Home</a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" aria-current="page" href="collection.php">Collection</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="convention.php">Convention</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="login.php">Admin</a>
    </li>
</ul>

<?php
if (isset($_SESSION["status"])) {
    echo '<form method ="POST" id="one" >';
    echo '<input type="submit" value="Logout" class="btn" name="logout" style="box-shadow: none !important; margin-top: 4px;"/>';
    echo '</form>';
}
?>

</div><!-- /.navbar-collapse -->
</div><!-- /.container-fluid -->
</nav>
<br>
<form method="POST" name="comicForm">
    <table>
        <th colspan="2">Welcome <?= $_SESSION['name'] ?>
        <td><input type="submit" value="Search" name="filterForm" class="btn" />
        </td>
        <td>
            <input type="submit" value="All Comics" name="allIn" class="btn" /></span>
        </td>
        </th>
        <tr>
            <td><label id="l1">Title:</label> <input type="text" name="title" size="30" placeholder="enter comic title here." />
            </td>
            <td><label id="l2">Creator:</label> <input type="text" name="creator" size="20" placeholder="first or last name" />
            </td>
            <td><label id="l3">publisher:</label>
                <select name="publisher">
                    <option value="" disabled selected>select one</option>
                    <?php
                    $allPub = get('comicBook', 'publisher');
                    //print_r($allPub);
                    foreach ($allPub as $singlePub) {
                        echo "<option>" . $singlePub['publisher'] . " </option>";
                    }
                    ?>
                </select>
            </td>
            <td><label id="l4">Sort By:</label>
                <select name="sortBy">
                    <option value="" disabled selected>select one</option>
                    <option value="title">Title</option>
                    <option value="creator">Creator</option>
                    <option value="year ASC">Year: Low to High</option>
                    <option value="year DESC">Year: High to Low</option>
                    <option value="issue ASC">Issue: Low to High</option>
                    <option value="issue DESC">Issue: High to Low</option>
                </select>
            </td>
        </tr>
    </table>
</form>

<form method="POST" action="">
    <div class="wrapper">
        <table class="table table-condensed" id="comDisplay">
            <!--https://www.w3schools.com/bootstrap/bootstrap_tables.asp-->
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Creator</th>
                    <th>Issue</th>
                    <th>Publisher</th>
                    <th>Year</th>
                    <th>Autograph</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($_POST['filterForm'])) {
                    $filterList = goSQLcomic("comicBook");
                    dataDisplay($filterList);
                } else { // Display inventory initially.
                    $comic = getInfo("comicBook");
                    dataDisplay($comic);
                }
                ?>
            </tbody>
        </table>
    </div>
</form>

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="modalTitle"></h4>
            </div>
            <div class="modal-body">
                <div id="modalInfo"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<?php include 'footer.inc' ?>

<script>
    //https://datatables.net/reference/option
    $(document).ready(function() {
        $('#comDisplay').DataTable({
            "lengthMenu": [10, 15, 30],
            "searching": false,
            "ordering": false
        });
    });
</script>
</body>

</html>