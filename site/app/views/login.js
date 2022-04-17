document.querySelector('#login-form button[type=submit]').addEventListener('click', e => {

    e.preventDefault()
    post(
        '/?action=login',
        {
            login: document.querySelector('#login').value,
            password: document.querySelector('#password').value,
        },
        data => {

            if(data.success)
            {
                window.location = 'http://localhost:8300/?action=register&jwt=' + data.jwt + '&to=' + document.baseURI
            }
            else
            {
                alert('Nope')
            }
        }
    )
})

document.querySelectorAll('#login-form button[data-login]').forEach(btn => {
    
    btn.addEventListener('click', e => {

        e.preventDefault()
        document.querySelector('#login').value = btn.dataset.login
        document.querySelector('#password').value = btn.dataset.password
    })
})