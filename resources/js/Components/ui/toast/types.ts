import type { Component } from 'vue'

export interface ToastAction {
  label: string
  onClick: () => void
}

export interface ToastState {
  id: string
  title: string
  description?: string
  action?: ToastAction
  duration: number
  open: boolean
  // Add other properties you might use (e.g., variant)
}