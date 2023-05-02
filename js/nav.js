// Define separate arrays for sideNav and topNav items
const sideNavItems = [
  { title: 'Method', href: '#' },
  { title: 'Progressions', href: '#' },
  { title: 'Exercises', href: 'exercises.php' },
  { title: 'Workouts', href: 'workouts.php' },
];
let topNavItems = [
  { title: 'Log In', href: 'login.php' },
  { title: 'Create Account', href: 'create_account.php' },
];
if (window.location.pathname.includes('create_account.php')) {
  topNavItems = topNavItems.filter(item => item.title !== 'Create Account');
}
if (window.location.pathname.includes('login.php')) {
  topNavItems = topNavItems.filter(item => item.title !== 'Log In');
}
// Function to fetch session variables from the server
function fetchSessionVars() {
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (xhr.readyState === XMLHttpRequest.DONE) {
        if (xhr.status === 200) {
          const sessionVars = JSON.parse(xhr.responseText);
          resolve(sessionVars);
        } else {
          reject();
        }
      }
    };
    xhr.open('GET', 'php/get_session_vars.php', true);
    xhr.send();
  });
}
// Function to update navigation items based on session variables
function updateNavigation(sessionVars) {
  window.sessionVars = sessionVars;
  console.log(sessionVars.userId)
  console.log(sessionVars.userName);
  if (sessionVars.userName) {
    topNavItems = topNavItems.filter(item => item.title !== 'Create Account');
    topNavItems = topNavItems.filter(item => item.title !== 'Log In');
    topNavItems.push({ title: 'Logout', href: 'php/logout.php' });
  }
  const topNav = document.querySelector('#top-nav');
  const sideNav = document.querySelector('#side-nav');
  // Initialize sideNav
  var elems = document.querySelectorAll('.sidenav');
  var instances = M.Sidenav.init(elems);
  // Clear existing items
  sideNav.innerHTML = '';
  topNav.innerHTML = '';
  // Add items to sideNav
  sideNavItems.forEach((item) => {
    const li = document.createElement('li');
    const a = document.createElement('a');
    a.textContent = item.title;
    a.href = item.href;
    li.appendChild(a);
    sideNav.appendChild(li);
  });
  // Add items to topNav
  topNavItems.forEach((item) => {
    const li = document.createElement('li');
    const a = document.createElement('a');
    a.textContent = item.title;
    a.href = item.href;
    li.appendChild(a);
    topNav.appendChild(li);
  });
  // Make top-nav always visible
  topNav.classList.remove('hide');
}
document.addEventListener('DOMContentLoaded', function () {
  fetchSessionVars().then(updateNavigation);
});
