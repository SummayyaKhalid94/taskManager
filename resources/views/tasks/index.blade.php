@extends('layouts.app')

@section('content')
    <div class="pagetitle">
        <h1>Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                <li class="breadcrumb-item active">{{ __('Tasks') }}</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->


    <section class="section dashboard">
        <div class="row">

            <div class="col-lg-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row d-flex justify-content-between">
                            <div class="col-md-6 d-flex">
                                <select class="form-select" id="filterProjectName" name="filterProjectName" required>
                                    <option value="" disabled selected>Filter by Projects</option>
                                    <option value="Project A" @if ($project->project_name == 'Project A') selected @endif>Project A
                                    </option>
                                    <option value="Project B" @if ($project->project_name == 'Project B') selected @endif>Project B
                                    </option>
                                    <option value="Project C" @if ($project->project_name == 'Project C') selected @endif>Project C
                                    </option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary col-md-2" data-bs-toggle="modal"
                                data-bs-target="#addTaskModal">
                                {{ __('Add Task') }}
                            </button>
                        </div>

                        <br><br>
                        <table id="taskTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Priority</th>
                                    <th>Name</th>
                                    <th>Project Name</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Tasks will be listed here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>


    <div class="modal fade" id="addTaskModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createTaskForm">
                        @csrf
                        <div class="mb-3">
                            <label for="taskName" class="form-label">Task Name</label>
                            <input type="text" class="form-control" id="taskName" name="taskName" required>
                        </div>
                        <div class="mb-3">
                            <label for="projectName" class="form-label">Project Name</label>
                            <select class="form-select" id="projectName" name="projectName" required>
                                <option value="" selected disabled>Select Project</option>
                                <option value="Project A">Project A</option>
                                <option value="Project B">Project B</option>
                                <option value="Project C">Project C</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Create Task</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editTaskModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editTaskForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="taskId" name="taskId">
                        <div class="mb-3">
                            <label for="editTaskName" class="form-label">Task Name</label>
                            <input type="text" class="form-control" id="editTaskName" name="taskName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editProjectName" class="form-label">Project Name</label>
                            <select class="form-select" id="editProjectName" name="projectName" required>
                                <option value="" selected disabled>Select Project</option>
                                <option value="Project A">Project A</option>
                                <option value="Project B">Project B</option>
                                <option value="Project C">Project C</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        var dataTable;
        var deleteRoute;
        $(document).ready(function() {
            dataTable = $('#taskTable').DataTable({
                processing: true,
                serverSide: true,
                rowReorder: true,
                pageLength: 100,
                ajax: "{{ route('task.list', ['project' => $project->project_name]) }}",
                responsive: true,
                createdRow: function(row, data, dataIndex) {
                    // Add 'id' as a custom attribute to the row
                    $(row).attr('data-id', data.id);
                },
                columns: [{
                        data: 'priority',
                        render: function(data, type, full, meta) {

                            return '<i class="bx bx-move"></i> ' + data;

                        }
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'project_name'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'updated_at'
                    },
                    {
                        targets: -1,
                        data: 'action'
                    },
                ],
                rowReorder: {
                    dataSrc: 'priority'
                },
                order: [
                    [0, 'asc']
                ],
                columnDefs: [{
                    targets: [0, 5],
                    orderable: false
                }]
            });

            dataTable.on('row-reordered', function(e, diff, edit) {
                // 'diff' contains information about the reordered rows, including their old and new positions
                // 'edit.triggerRow' contains the row that triggered the reorder (the row that was dragged)

                $('.loader-container').show();
                var reorderedData = [];

                // Loop through the diff object to extract row IDs and new positions
                $.each(diff, function(index, value) {
                    var rowId = $(value.node).data('id');
                    reorderedData.push({
                        id: rowId,
                        new_position: value
                            .newPosition
                    });
                });

                var pageInfo = dataTable.page.info();

                // Send the reordered data to the server via AJAX
                if (reorderedData.length) {
                    $.ajax({
                        url: "{{ route('task.reorder') }}",
                        method: 'POST',
                        data: {
                            reorderedData: reorderedData,
                            page: pageInfo.page + 1, // DataTable uses zero-based index
                            per_page: pageInfo.length,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $('.loader-container').hide();
                            // Handle the success response from the server
                            toastr.success(response.message);
                            dataTable.ajax.reload();
                        },
                        error: function(xhr, status, error) {
                            // Handle errors, if any
                            $('.loader-container').hide();
                            toastr.error(JSON.parse(xhr.responseText).message);
                            // console.error(xhr.responseText);
                        }
                    });
                } else {
                    dataTable.ajax.reload();
                    $('.loader-container').hide();
                }
            });

            // Handle form submission for adding a new task
            $('#createTaskForm').submit(function(event) {
                event.preventDefault();
                $('.loader-container').show();
                var formData = $(this).serialize();
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                $.ajax({
                    url: "{{ route('task.store') }}",
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        $('.loader-container').hide();
                        toastr.success(response.message);
                        // Change the value of select #filterProjectName
                        var projectName = $('[name="projectName"]').val();
                        $('#filterProjectName').val(projectName);
                        $('#filterProjectName').trigger('change');
                        dataTable.ajax.reload();
                        document.querySelector('#createTaskForm').reset();
                        $('#addTaskModal').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        $('.loader-container').hide();
                        toastr.error(JSON.parse(xhr.responseText).message);
                    }
                });
            });

            // Handle form submission for editing a task
            $('#editTaskForm').submit(function(event) {
                event.preventDefault();
                $('.loader-container').show();
                var formData = $(this).serialize();
                var taskId = $('#editTaskForm input[name=id]').val();
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                $.ajax({
                    url: "{{ route('task.update', ':id') }}".replace(':id',
                        taskId),
                    type: 'PUT',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        $('.loader-container').hide();
                        toastr.success(response.message);
                        dataTable.ajax.reload();
                        document.querySelector('#editTaskForm').reset();
                        $('#editTaskModal').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        $('.loader-container').hide();
                        toastr.error(JSON.parse(xhr.responseText).message);
                    }
                });
            });


            // handle changing project from select dropdown
            $('#filterProjectName').on('change', function() {
                var projectName = $(this).val();
                var ajaxUrl = "{{ route('task.list') }}?project=" + projectName;

                // Update the browser's URL without reloading the page
                var newUrl = window.location.protocol + '//' + window.location.host + window.location
                    .pathname + '?project=' + projectName;
                window.history.pushState({
                    path: newUrl
                }, '', newUrl);

                updateDataTable(ajaxUrl);
            });


        });

        function updateDataTable(ajaxUrl) {
            dataTable.ajax.url(ajaxUrl).load();
        }

        function loadTaskData(show_route, task_id) {
            $('.loader-container').show();
            $('#editTaskForm input[name="id"]').remove();
            $('#editTaskForm').append('<input type="hidden" name="id" value="' + task_id + '">');
            $.ajax({
                url: show_route,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // toastr.success(response.message);
                    $('.loader-container').hide();
                    $('#editTaskForm input[name="taskName"]').val(response.result.name);
                    $('#editTaskForm #editProjectName').val(response.result.project_name).trigger('change');
                    $('#editTaskModal').modal('show');
                },
                error: function(xhr, status, error) {
                    $('.loader-container').hide();
                    toastr.error(JSON.parse(xhr.responseText).message);
                }
            });
        }
    </script>
@endsection
