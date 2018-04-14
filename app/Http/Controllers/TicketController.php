<?php

namespace App\Http\Controllers;

use App\Order;
use App\Ticket;
use App\User;
use Carbon\Carbon;

class TicketController extends Controller
{
    public function index()
    {
        $opens = new Carbon('2018-04-20 17:30:00', new \DateTimeZone('Europe/Amsterdam'));

        if (now() < $opens && app()->environment() !== 'local') {
            return view('tickets.closed', [
                'opens' => $opens,
            ]);
        }

        return view('tickets.index', [
            'tickets' => Ticket::all(),
        ]);
    }

    /**
     * @param Ticket $ticket
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        return view('tickets.show', [
            'ticket' => $ticket,
        ]);
    }

    /**
     * @param Ticket $ticket
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function buy(Ticket $ticket)
    {
        $this->authorize('buy', $ticket);

        /** @var User $user */
        $user = auth()->user();

        /** @var Order $order */
        $order = $user->orders()->make();
        $order->ticket()->associate($ticket);

        $order->price = $ticket->price;

        $order->save();

        if ($ticket->stock !== null) {
            $ticket->decrement('stock');
        }

        session()->flash('alert-success', [
            'title' => 'Ticket bought.',
            'message' => [
                'Congratulations, you have bought a ticket!',
            ],
        ]);

        return redirect()->route('orders');
    }
}