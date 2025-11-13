import { shallowRef } from 'vue'
import type { ToastState, ToastAction } from '@/Components/ui/toast/types'

// This composable manages the global state of toast notifications.

const toasts = shallowRef<ToastState[]>([])

let count = 0
function genId() {
  // Simple ID generator
  count = (count + 1) % Number.MAX_SAFE_INTEGER
  return count.toString()
}

/**
 * Provides functions to trigger and manage toast notifications.
 */
export function useToast() {
  /**
   * Triggers a new toast notification.
   */
  const toast = ({ title, description, action, duration = 3000 }: {
    title: string
    description?: string
    action?: ToastAction
    duration?: number
  }) => {
    const id = genId()
    const newToast: ToastState = {
      id,
      title,
      description,
      action,
      duration,
      open: true,
    }

    toasts.value.push(newToast)

    if (duration > 0) {
      setTimeout(() => {
        dismiss(id)
      }, duration)
    }

    return {
      id: newToast.id,
      dismiss: () => dismiss(id),
      update: (newProps: Partial<ToastState>) => updateToast(id, newProps),
    }
  }

  /**
   * Dismisses a specific toast by setting its 'open' state to false.
   */
  const dismiss = (id: string) => {
    updateToast(id, { open: false })
    // The Toaster.vue component handles removing the toast from the array 
    // after the transition completes, triggered by the update:open event.
  }

  /**
   * Updates properties of an existing toast.
   */
  const updateToast = (id: string, newProps: Partial<ToastState>) => {
    const index = toasts.value.findIndex(t => t.id === id)
    if (index !== -1) {
      toasts.value[index] = { ...toasts.value[index], ...newProps }
    }
  }

  return {
    toasts,
    toast,
    dismiss,
  }
}

export type { ToastState, ToastAction }