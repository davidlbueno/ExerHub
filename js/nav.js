// Define separate arrays for sideNav and topNav items
const sideNavItems = [
  { title: 'Method', href: '#' },
  { title: 'Progressions', href: 'progressions.php' },
  { title: 'Exercises', href: 'exercises.php' },
  { title: 'Workouts', href: 'workouts.php' },
  { title: 'Logs', href: 'logs.php' },
];
let topNavItems = [
  { title: 'arrow_back', href: 'javascript:;', class: 'back-button' },
];
if (window.location.pathname.includes('create_account.php')) {
  topNavItems = topNavItems.filter(item => item.title !== 'Create Account');
}
if (window.location.pathname.includes('login.php')) {
  topNavItems = topNavItems.filter(item => item.title !== 'Log In');
}
if (window.location.pathname.includes('index.html')) {
  topNavItems = topNavItems.filter(item => item.title !== 'arrow_back');
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
    xhr.open('GET', '/php/get_session_vars.php', true);
    xhr.send();
  });
}
// Function to update navigation items based on session variables
function updateNavigation(sessionVars) {
  window.sessionVars = sessionVars;
  console.log(sessionVars);
  //if (sessionVars.userName) {
  //  topNavItems = topNavItems.filter(item => item.title !== 'Create Account');
  //  topNavItems = topNavItems.filter(item => item.title !== 'Log In');
  //  topNavItems.push({ title: 'Logout', href: 'php/logout.php' });
  //}
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

 // Add my account link to sideNav
 if (sessionVars.userName) {
  const usernameLink = document.createElement('a');
  usernameLink.textContent = 'My Account';
  usernameLink.style.display = 'block';
  usernameLink.className = 'btn';
  usernameLink.href = 'account.php';
  sideNav.appendChild(usernameLink);
// Add logout link to sideNav
  const logoutLink = document.createElement('a');
  logoutLink.textContent = 'Logout';
  logoutLink.style.display = 'block';
  logoutLink.className = 'btn';
  logoutLink.href = 'php/logout.php';
  sideNav.appendChild(logoutLink);
} else {
  // Add login link to sideNav
  const loginLink = document.createElement('a');
  loginLink.textContent = 'Login';
  loginLink.style.display = 'block';
  loginLink.className = 'btn';
  loginLink.href = 'login.php';
  sideNav.appendChild(loginLink);
  // Add create account link to sideNav
  const createAccountLink = document.createElement('a');
  createAccountLink.textContent = 'Create Account';
  createAccountLink.style.display = 'block';
  createAccountLink.className = 'btn';
  createAccountLink.href = 'create_account.php';
  sideNav.appendChild(createAccountLink);
}

  // Add items to topNav
topNavItems.forEach((item) => {
  const a = document.createElement('a');
  a.href = item.href;
  a.className = item.class;

  const span = document.createElement('span');
  span.className = 'material-icons';
  span.textContent = item.title;

  a.appendChild(span);

  a.onclick = function(event) {
    event.preventDefault(); // Prevent default navigation
    if (item.title === 'arrow_back') {
      history.back();
    }
  };
  
  topNav.appendChild(a);
});
  // Add CSS style to align the buttons to the right
  topNav.style.textAlign = 'right';
  // Make top-nav always visible
  topNav.classList.remove('hide');
}
document.addEventListener('DOMContentLoaded', function () {
  fetchSessionVars().then(updateNavigation);
  // Get the current page title and URL
  const pageTitle = document.title;
  const pageURL = window.location.pathname.split('/').pop();
  // Get the dynamic-navbar element
  const dynamicNavbar = document.getElementById("dynamic-navbar");
  // Create the dynamic content
  const a = document.createElement('a');
  a.href = pageURL;
  a.textContent = pageTitle;
  // Append the dynamic content to the navbar
  dynamicNavbar.appendChild(a);
});
