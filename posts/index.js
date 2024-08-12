
function confirmDelete() {
    return confirm("Are you sure you want to delete this post?");
}

function confirmLogout() {
    return confirm("Are you sure you want to Logout?");
}


function likeBlog(blog_id) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "../comments/likes.php?blog_id=" + blog_id, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            if (xhr.responseText === "success") {
                // Update like count on the page
                var likeCountElement = document.getElementById("like-count-" + blog_id);
                if (likeCountElement) {
                    var currentLikes = parseInt(likeCountElement.innerText);
                    likeCountElement.innerText = currentLikes + 1;
                }
            } else {
                // Handle error
                console.error("Failed to like blog.");
            }
        }
    };
    xhr.send();
}
