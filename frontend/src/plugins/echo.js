import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

const echo = new Echo({
  broadcaster: 'pusher',
  key: '19772c28cd07f68e18cf',
  cluster: 'us2',
  forceTLS: true,
})

export default echo
