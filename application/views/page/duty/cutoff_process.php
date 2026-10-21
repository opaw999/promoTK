<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Cutoffs to Process</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">Setup</li>
                        <li class="breadcrumb-item active">Cutoffs to Process</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <?php
            foreach ($cutoffs as $row) {

                if ($row['endFC'] == '') {
                    $endFC = 'Last';
                } else {
                    $endFC = $row['endFC'];
                }

                $statCut = $row['statCut'];
                $sched = $row['startFC'] . '-' . $endFC . ' | ' . $row['startSC'] . '-' . $row['endSC'];

                $df = '';
                $dt = '';
                $cond = "statCut='$statCut' ORDER BY coId DESC LIMIT 1";
                $date = $this->promo_model->select_rowArray('tk', 'setup_cutoff_pro', $cond);
                if ($date) {
                    $df = $date['dateFrom'];
                    $dt = $date['dateTo'];
                }

                echo '<div class="col-md-3">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><b>[ ' . $sched . ' ]</b></h3>
                            </div>
                            <form id="cutoff_' . $statCut . '" novalidate="novalidate">
                                <input type="hidden" name="sched_' . $statCut . '" value="[ ' . $sched . ' ]">
                                <input type="hidden" name="statCut" value="' . $statCut . '">
                                <div class="card-body">
                                    <div class="row">
                                      <label for="dateFrom_' . $statCut . '" class="col-sm-5 col-form-label">Date From</label>
                                      <div class="form-group col-sm-7">
                                        <input type="text" class="form-control datePicker datetimepicker-input" value="' . $df . '" id="dateFrom_' . $statCut . '" name="dateFrom_' . $statCut . '" data-toggle="datetimepicker" data-target="#dateFrom_' . $statCut . '" placeholder="yyyy-mm-dd" autocomplete="off" required>
                                      </div>
                                    </div>
                                    <div class="row">
                                      <label for="dateTo_' . $statCut . '" class="col-sm-5 col-form-label">Date To</label>
                                      <div class="form-group col-sm-7">
                                        <input type="text" class="form-control datePicker datetimepicker-input" value="' . $dt . '" id="dateTo_' . $statCut . '" name="dateTo_' . $statCut . '" data-toggle="datetimepicker" data-target="#dateTo_' . $statCut . '" placeholder="yyyy-mm-dd" autocomplete="off" required>
                                      </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>';
                echo '</div>';
            }
            ?>


        </div>
    </section>
</div>