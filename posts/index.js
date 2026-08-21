function confirmDelete() {
    return confirm("Are you sure you want to delete this post?");
}

function confirmLogout() {
    return confirm("Are you sure you want to Logout?");
}

function likeBlog(blogId, csrfToken) {
    const body = new URLSearchParams({
        blog_id: String(blogId),
        csrf_token: csrfToken
    });

    fetch("../comments/likes.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
            "X-Requested-With": "XMLHttpRequest"
        },
        body: body.toString()
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || "Unable to like the post.");
            }

            const likeCountElement = document.getElementById("like-count-" + blogId);
            if (likeCountElement) {
                likeCountElement.textContent = data.likes;
            }
        })
        .catch(error => console.error(error.message));
}
