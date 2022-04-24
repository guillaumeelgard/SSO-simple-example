document.querySelector('#login-form button[type=submit]').addEventListener('click', e => {

    e.preventDefault()

    fetch('/?action=login', {
        method: 'POST',
        body: JSON.stringify({
            login: document.querySelector('#login').value,
            password: document.querySelector('#password').value,
        })
    })
    .then(r => r.json())
    .then(data => {

        if(data.success)
        {
            window.location = authAddress + '/?action=register&jwt=' + data.jwt + '&to=' + document.baseURI
        }
        else
        {
            alert('Nope')
        }
    })
})

document.querySelectorAll('#login-form button[data-login]').forEach(btn => {
    
    btn.addEventListener('click', e => {

        e.preventDefault()
        document.querySelector('#login').value = btn.dataset.login
        document.querySelector('#password').value = btn.dataset.password
    })
})