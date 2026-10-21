<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Date Assigned</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">View</li>
                        <li class="breadcrumb-item active">Date Assigned</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Viewing of Employee Assigned</h3>
                    </div>
                    <div class="card-body">

                        <div class="form-group">
                            <label for="dateAssigned">Select Date</label>
                            <input type="text" class="form-control datetimepicker-input" id="dateAssigned" name="dateAssigned" onchange="getList(this.value)" data-toggle="datetimepicker" data-target="#dateAssigned" placeholder="yyyy-mm-dd" autocomplete="off" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Employee List</h3>
                    </div>
                    <div class="card-body assign_list">
                        <!-- <table class="table table-sm table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>HRMS ID</th>
                                    <th>Name</th>
                                    <th>Cutoff</th>
                                    <th>Assigned By</th>
                                </tr>
                            </thead>
                        </table> -->
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>