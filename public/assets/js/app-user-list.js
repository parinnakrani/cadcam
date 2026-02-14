/**
 * User List - DataTables Implementation
 */
'use strict';

$(function () {
  var siteBaseUrl = (typeof baseUrl !== 'undefined') ? baseUrl : '';
  var dt_user_table = $('.datatables-users');
  var statusObj = {
    'Active': { title: 'Active', class: 'bg-label-success' },
    'Inactive': { title: 'Inactive', class: 'bg-label-secondary' },
    'Pending': { title: 'Pending', class: 'bg-label-warning' },
    'Terminated': { title: 'Terminated', class: 'bg-label-danger' }
  };

  // Load Stats
  $.ajax({
    url: siteBaseUrl + 'api/users/stats',
    method: 'GET',
    success: function(data) {
      $('#stat-total').text(data.total || 0);
      $('#stat-active').text(data.active || 0);
      $('#stat-inactive').text(data.inactive || 0);
      $('#stat-pending').text(data.pending || 0);
    }
  });

  // Load Roles for filter dropdown
  $.ajax({
    url: siteBaseUrl + 'api/users/roles',
    method: 'GET',
    success: function(roles) {
      var $select = $('#UserRole');
      if (roles && roles.length) {
        roles.forEach(function(role) {
          $select.append('<option value="' + role.id + '">' + role.role_name + '</option>');
        });
      }
    }
  });

  // DataTable
  var dt_user = null;
  if (dt_user_table.length) {
    dt_user = dt_user_table.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: siteBaseUrl + 'api/users/list',
        type: 'GET',
        data: function(d) {
          d.role = $('#UserRole').val() || '';
          d.status = $('#UserStatus').val() || '';
        }
      },
      columns: [
        { data: 'full_name' },
        { data: 'role' },
        { data: 'status' },
        { data: 'mobile' },
        { data: null }
      ],
      columnDefs: [
        {
          // User column - name with avatar and email
          targets: 0,
          render: function(data, type, full) {
            var name = full.full_name || '';
            var email = full.email || '';
            var initials = name.match(/\b\w/g) || [];
            initials = ((initials.shift() || '') + (initials.pop() || '')).toUpperCase();
            var states = ['success', 'danger', 'warning', 'info', 'primary', 'secondary'];
            var state = states[Math.floor(Math.random() * 6)];
            
            return '<div class="d-flex justify-content-start align-items-center user-name">' +
              '<div class="avatar-wrapper"><div class="avatar avatar-sm me-3">' +
              '<span class="avatar-initial rounded-circle bg-label-' + state + '">' + initials + '</span>' +
              '</div></div>' +
              '<div class="d-flex flex-column">' +
              '<a href="' + full.edit_url + '" class="text-truncate text-heading"><span class="fw-medium">' + name + '</span></a>' +
              '<small class="text-muted">' + email + '</small></div></div>';
          }
        },
        {
          // Role column
          targets: 1,
          render: function(data, type, full) {
            var role = full.role || 'N/A';
            return '<span class="text-truncate d-flex align-items-center text-heading">' +
              '<i class="ri-shield-user-line ri-22px text-primary me-2"></i>' + role + '</span>';
          }
        },
        {
          // Status column
          targets: 2,
          render: function(data, type, full) {
            var s = statusObj[full.status] || { title: full.status || 'Unknown', class: 'bg-label-secondary' };
            return '<span class="badge rounded-pill ' + s.class + '">' + s.title + '</span>';
          }
        },
        {
          // Mobile column
          targets: 3,
          render: function(data, type, full) {
            return '<span class="text-heading">' + (full.mobile || '-') + '</span>';
          }
        },
        {
          // Actions column
          targets: -1,
          orderable: false,
          searchable: false,
          render: function(data, type, full) {
            return '<div class="d-flex align-items-center">' +
              '<a href="' + full.edit_url + '" class="btn btn-icon btn-text-secondary rounded-pill waves-effect" data-bs-toggle="tooltip" title="View"><i class="ri-eye-line ri-20px"></i></a>' +
              '<a href="' + full.edit_url + '" class="btn btn-icon btn-text-secondary rounded-pill waves-effect" data-bs-toggle="tooltip" title="Edit"><i class="ri-edit-box-line ri-20px"></i></a>' +
              '<a href="' + full.delete_url + '" class="btn btn-icon btn-text-secondary rounded-pill waves-effect" data-bs-toggle="tooltip" title="Delete" onclick="return confirm(\'Are you sure you want to delete this user?\');"><i class="ri-delete-bin-7-line ri-20px"></i></a>' +
              '</div>';
          }
        }
      ],
      order: [[0, 'asc']],
      dom:
        '<"row m-2 my-0 mt-0 justify-content-end"' +
          '<"d-md-flex align-items-center dt-layout-end col-md-auto ms-auto d-flex gap-md-4 justify-content-md-between justify-content-center gap-md-2 flex-wrap mt-0"' +
            '<"dt-search"f>' +
            '<"add-new-btn">' +
          '>' +
        '>' +
        't' +
        '<"row mx-1"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      language: {
        sLengthMenu: 'Show _MENU_',
        search: '',
        searchPlaceholder: 'Search User',
        paginate: {
          next: '<i class="ri-arrow-right-s-line"></i>',
          previous: '<i class="ri-arrow-left-s-line"></i>'
        }
      },
      responsive: true,
      initComplete: function() {
        // Add the "Add New User" button
        $('.add-new-btn').html(
          '<a href="' + siteBaseUrl + 'users/create" class="btn add-new btn-primary waves-effect waves-light">' +
          '<i class="ri-add-line ri-16px me-0 me-sm-2 d-sm-none d-inline-block"></i>' +
          '<span class="d-none d-sm-inline-block">Add New User</span></a>'
        );
      }
    });

    // Filter change handlers
    $('#UserRole, #UserStatus').on('change', function() {
      dt_user.ajax.reload();
    });
  }
});
