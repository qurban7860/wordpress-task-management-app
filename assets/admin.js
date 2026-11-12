document.addEventListener('DOMContentLoaded', function () {
  const root = document.getElementById('tma-app-root');
  if (!root || typeof tmaAppData === 'undefined') {
    return;
  }

  const state = {
    workspaces: [],
    tasks: [],
    activity: [],
    selectedWorkspaceId: null,
    isLoading: true,
  };

  const buildLayout = () => {
    root.innerHTML = `
      <div class="tma-shell">
        <aside class="tma-sidebar">
          <h2>Workspaces</h2>
          <ul class="tma-workspace-list" id="tma-workspace-list"></ul>
          <div class="tma-toolbar" style="margin-top: 16px;">
            <input id="tma-workspace-name" class="tma-input" type="text" placeholder="New workspace name" />
            <button id="tma-create-workspace" class="tma-button secondary">Add workspace</button>
          </div>
        </aside>
        <main class="tma-main-panel">
          <h2>Tasks</h2>
          <div class="tma-toolbar">
            <input id="tma-task-search" class="tma-input" type="search" placeholder="Search tasks" style="max-width: 260px;" />
            <select id="tma-task-status-filter" class="tma-select" style="max-width: 200px;">
              <option value="">All statuses</option>
              <option value="todo">To do</option>
              <option value="in-progress">In progress</option>
              <option value="done">Done</option>
            </select>
            <select id="tma-task-priority-filter" class="tma-select" style="max-width: 180px;">
              <option value="">All priorities</option>
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
            </select>
            <button id="tma-refresh" class="tma-button secondary">Refresh</button>
            <button id="tma-new-task" class="tma-button">New task</button>
          </div>

          <div id="tma-task-form" style="display:none; margin-bottom: 20px;">
            <div class="tma-grid">
              <div>
                <label>Title</label>
                <input id="tma-task-title" class="tma-input" type="text" />
              </div>
              <div>
                <label>Priority</label>
                <select id="tma-task-priority" class="tma-select">
                  <option value="low">Low</option>
                  <option value="medium" selected>Medium</option>
                  <option value="high">High</option>
                </select>
              </div>
              <div>
                <label>Status</label>
                <select id="tma-task-status" class="tma-select">
                  <option value="todo" selected>To do</option>
                  <option value="in-progress">In progress</option>
                  <option value="done">Done</option>
                </select>
              </div>
              <div>
                <label>Due date</label>
                <input id="tma-task-due-date" class="tma-input" type="date" />
              </div>
            </div>
            <div style="margin-top: 12px;">
              <label>Description</label>
              <textarea id="tma-task-description" class="tma-textarea" rows="4"></textarea>
            </div>
            <div class="tma-toolbar" style="margin-top: 12px;">
              <button id="tma-save-task" class="tma-button">Save task</button>
              <button id="tma-cancel-task" class="tma-button secondary">Cancel</button>
            </div>
          </div>

          <ul id="tma-task-list" class="tma-task-list"></ul>

          <h2 style="margin-top: 24px;">Recent activity</h2>
          <ul id="tma-activity-list" class="tma-activity-list"></ul>
        </main>
      </div>
    `;

    bindEvents();
  };

  const renderWorkspaceList = () => {
    const list = document.getElementById('tma-workspace-list');
    if (!list) return;

    list.innerHTML = state.workspaces.map((workspace) => `
      <li class="tma-workspace-item ${workspace.id === state.selectedWorkspaceId ? 'is-selected' : ''}" data-id="${workspace.id}">
        ${workspace.name}
      </li>
    `).join('');

    list.querySelectorAll('.tma-workspace-item').forEach((item) => {
      item.addEventListener('click', () => {
        state.selectedWorkspaceId = Number(item.dataset.id);
        renderWorkspaceList();
        loadTasks();
        loadActivity();
      });
    });
  };

  const renderTasks = () => {
    const list = document.getElementById('tma-task-list');
    if (!list) return;

    if (!state.tasks.length) {
      list.innerHTML = '<li class="tma-activity-item">No tasks yet in this workspace.</li>';
      return;
    }

    list.innerHTML = state.tasks.map((task) => {
      const badgeClass = task.status === 'done' ? 'success' : task.priority === 'high' ? 'warning' : '';
      return `
        <li class="tma-task-card" data-id="${task.id}">
          <div class="tma-task-meta">
            <span class="tma-badge ${badgeClass}">${task.priority}</span>
            <span>${task.status}</span>
          </div>
          <h3>${task.title}</h3>
          <p>${task.description || 'No description provided.'}</p>
          <p><strong>Due:</strong> ${task.due_date || 'No due date'}</p>
          <div class="tma-toolbar">
            <button class="tma-button secondary tma-edit-task" data-id="${task.id}">Edit</button>
            <button class="tma-button secondary tma-delete-task" data-id="${task.id}">Delete</button>
          </div>
        </li>
      `;
    }).join('');

    list.querySelectorAll('.tma-edit-task').forEach((button) => {
      button.addEventListener('click', () => {
        const id = Number(button.dataset.id);
        const task = state.tasks.find((item) => item.id === id);
        if (!task) return;
        populateTaskForm(task);
      });
    });

    list.querySelectorAll('.tma-delete-task').forEach((button) => {
      button.addEventListener('click', () => {
        const id = Number(button.dataset.id);
        fetch(`${tmaAppData.restUrl}/tasks/${id}`, {
          method: 'DELETE',
          headers: {
            'X-WP-Nonce': tmaAppData.nonce,
            'Content-Type': 'application/json',
          },
        })
          .then((response) => response.json())
          .then((payload) => {
            if (payload.success) {
              loadTasks();
              loadActivity();
            }
          });
      });
    });
  };

  const renderActivity = () => {
    const list = document.getElementById('tma-activity-list');
    if (!list) return;

    if (!state.activity.length) {
      list.innerHTML = '<li class="tma-activity-item">No recent activity.</li>';
      return;
    }

    list.innerHTML = state.activity.map((entry) => `
      <li class="tma-activity-item">
        <strong>${entry.action_type}</strong> — ${entry.description}
      </li>
    `).join('');
  };

  const bindEvents = () => {
    document.getElementById('tma-create-workspace')?.addEventListener('click', createWorkspace);
    document.getElementById('tma-refresh')?.addEventListener('click', refreshData);
    document.getElementById('tma-new-task')?.addEventListener('click', showTaskForm);
    document.getElementById('tma-save-task')?.addEventListener('click', saveTask);
    document.getElementById('tma-cancel-task')?.addEventListener('click', hideTaskForm);
    document.getElementById('tma-task-search')?.addEventListener('input', debounce(() => {
      loadTasks();
    }, 200));
    document.getElementById('tma-task-status-filter')?.addEventListener('change', loadTasks);
    document.getElementById('tma-task-priority-filter')?.addEventListener('change', loadTasks);
  };

  const refreshData = () => {
    loadWorkspaces();
  };

  function debounce(fn, timeout) {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), timeout);
    };
  }

  const createWorkspace = () => {
    const input = document.getElementById('tma-workspace-name');
    const name = input?.value.trim();
    if (!name) return;

    fetch(`${tmaAppData.restUrl}/workspaces`, {
      method: 'POST',
      headers: {
        'X-WP-Nonce': tmaAppData.nonce,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ name }),
    })
      .then((response) => response.json())
      .then((payload) => {
        if (payload.success) {
          input.value = '';
          loadWorkspaces();
        }
      });
  };

  const loadWorkspaces = () => {
    fetch(`${tmaAppData.restUrl}/workspaces`, {
      headers: { 'X-WP-Nonce': tmaAppData.nonce },
    })
      .then((response) => response.json())
      .then((payload) => {
        state.workspaces = payload.workspaces || [];
        if (!state.selectedWorkspaceId && state.workspaces.length) {
          state.selectedWorkspaceId = Number(state.workspaces[0].id);
        }
        renderWorkspaceList();
        if (state.selectedWorkspaceId) {
          loadTasks();
          loadActivity();
        }
      });
  };

  const loadTasks = () => {
    if (!state.selectedWorkspaceId) return;

    const search = document.getElementById('tma-task-search')?.value.trim() || '';
    const status = document.getElementById('tma-task-status-filter')?.value || '';
    const priority = document.getElementById('tma-task-priority-filter')?.value || '';
    const params = new URLSearchParams({
      workspace_id: String(state.selectedWorkspaceId),
      search,
      status,
      priority,
      sort: 'due_date',
      order: 'ASC',
    });

    fetch(`${tmaAppData.restUrl}/tasks?${params.toString()}`, {
      headers: { 'X-WP-Nonce': tmaAppData.nonce },
    })
      .then((response) => response.json())
      .then((payload) => {
        state.tasks = payload.tasks || [];
        renderTasks();
      });
  };

  const loadActivity = () => {
    if (!state.selectedWorkspaceId) return;

    fetch(`${tmaAppData.restUrl}/activity?workspace_id=${state.selectedWorkspaceId}`, {
      headers: { 'X-WP-Nonce': tmaAppData.nonce },
    })
      .then((response) => response.json())
      .then((payload) => {
        state.activity = payload.activity || [];
        renderActivity();
      });
  };

  const showTaskForm = () => {
    document.getElementById('tma-task-form').style.display = 'block';
  };

  const hideTaskForm = () => {
    document.getElementById('tma-task-form').style.display = 'none';
    document.getElementById('tma-task-title').value = '';
    document.getElementById('tma-task-description').value = '';
    document.getElementById('tma-task-due-date').value = '';
  };

  const populateTaskForm = (task) => {
    document.getElementById('tma-task-title').value = task.title || '';
    document.getElementById('tma-task-description').value = task.description || '';
    document.getElementById('tma-task-priority').value = task.priority || 'medium';
    document.getElementById('tma-task-status').value = task.status || 'todo';
    document.getElementById('tma-task-due-date').value = task.due_date || '';
    document.getElementById('tma-task-form').dataset.taskId = task.id;
    document.getElementById('tma-task-form').style.display = 'block';
  };

  const saveTask = () => {
    const payload = {
      workspace_id: state.selectedWorkspaceId,
      title: document.getElementById('tma-task-title').value,
      description: document.getElementById('tma-task-description').value,
      priority: document.getElementById('tma-task-priority').value,
      status: document.getElementById('tma-task-status').value,
      due_date: document.getElementById('tma-task-due-date').value,
    };

    const taskId = document.getElementById('tma-task-form').dataset.taskId;
    const method = taskId ? 'PUT' : 'POST';
    const endpoint = taskId ? `${tmaAppData.restUrl}/tasks/${taskId}` : `${tmaAppData.restUrl}/tasks`;

    fetch(endpoint, {
      method,
      headers: {
        'X-WP-Nonce': tmaAppData.nonce,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload),
    })
      .then((response) => response.json())
      .then((result) => {
        if (result.success) {
          hideTaskForm();
          loadTasks();
          loadActivity();
        }
      });
  };

  buildLayout();
  loadWorkspaces();
});
