@extends('errors::layout')

@section('title', __('errors.too_many_requests_title'))
@section('code', '429')
@section('message', __('errors.too_many_requests_message'))

