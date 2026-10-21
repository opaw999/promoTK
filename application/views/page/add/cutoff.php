<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Promo Cutoff</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">Add</li>
                        <li class="breadcrumb-item active">Promo Cutoff </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-5">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Add New Cutoff</h3>
                    </div>
                    <form id="add_cutoff" novalidate="novalidate">

                        <div class="card-body">
                            <h5 class="text-info">First Cutoff</h5>
                            <div class="row">
                                <label for="startFC" class="col-sm-2 col-form-label">Start</label>
                                <div class="form-group col-sm-10">
                                    <select class="form-control" name="startFC" id="startFC" onchange="firstDate(this.value)" required>
                                        <option value="">---</option>
                                        <?php
                                        for ($i = 1; $i <= 31; $i++) {

                                            $day = $i;
                                            if ($day < 10) {
                                                $day = '0' . $i;
                                            }
                                            echo ' <option value="' . $day . '">' . $day . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class=" row">
                                <label for="endFC" class="col-sm-2 col-form-label">End</label>
                                <div class="form-group col-sm-10">
                                    <select class="form-control" name="endFC" id="endFC" onchange="secondDate(this.value)" required>
                                        <option value="">---</option>
                                        <?php
                                        for ($i = 1; $i <= 31; $i++) {

                                            $day = $i;
                                            if ($day < 10) {
                                                $day = '0' . $i;
                                            }
                                            echo ' <option value="' . $day . '">' . $day . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <hr>
                            <h5 class="text-info">Second Cutoff</h5>
                            <div class="row">
                                <label for="startSC" class="col-sm-2 col-form-label">Start</label>
                                <div class="form-group col-sm-10">
                                    <input type="text" class="form-control" name="startSC" id="startSC" readonly required>
                                </div>
                            </div>
                            <div class="row">
                                <label for="endSC" class="col-sm-2 col-form-label">End</label>
                                <div class="form-group col-sm-10">
                                    <input type="text" class="form-control" name="endSC" id="endSC" readonly required>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Add</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">List of Cutoffs</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>1st Cutoff</th>
                                    <th>2nd Cutoff</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <?php
                            foreach ($cutoffs as $row) {

                                if ($row['endFC'] == '') {
                                    $endFC = 'Last';
                                } else {
                                    $endFC = $row['endFC'];
                                }

                                $statCut = $row['statCut'];


                                $status = '<span class="badge bg-warning">Inactive</span>';
                                if ($row['remark'] == 'active') {
                                    $status = '<span class="badge bg-success">Active</span>';
                                }


                                echo '<tr>';
                                echo '<td>' . $row['startFC'] . ' - ' . $endFC . '</td>';
                                echo '<td>' . $row['startSC'] . ' - ' . $row['endSC'] . '</td>';
                                echo '<td>' . $status . '</td>';
                                echo '<td></td>';
                                echo '</tr>';
                            }

                            ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>