<template>
    <div class="min-h-screen bg-gray-100 p-5 flex flex-col">
        <NavBar :username="username" :location="location" :terminal="terminal" />
        <slot />
    </div>
</template>

<script>
import NavBar from './NavBar.vue';

export default {
    components: {
        NavBar
    },
    computed: {
        username() {
            return this.$page.props.auth.user.name
        },
        location() {
            return this.$page.props.auth.env.location
        },
        terminal() {
            return this.$page.props.auth.env.terminal
        }
    }
};
</script>
