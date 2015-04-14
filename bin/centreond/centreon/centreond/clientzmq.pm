################################################################################
# Copyright 2005-2015 MERETHIS
# Centreon is developped by : Julien Mathis and Romain Le Merlus under
# GPL Licence 2.0.
# 
# This program is free software; you can redistribute it and/or modify it under 
# the terms of the GNU General Public License as published by the Free Software 
# Foundation ; either version 2 of the License.
# 
# This program is distributed in the hope that it will be useful, but WITHOUT ANY
# WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
# PARTICULAR PURPOSE. See the GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along with 
# this program; if not, see <http://www.gnu.org/licenses>.
# 
# Linking this program statically or dynamically with other modules is making a 
# combined work based on this program. Thus, the terms and conditions of the GNU 
# General Public License cover the whole combination.
# 
# As a special exception, the copyright holders of this program give MERETHIS 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of MERETHIS choice, provided that 
# MERETHIS also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
#
####################################################################################

package centreon::centreond::clientzmq;

use strict;
use warnings;
use centreon::centreond::common;
use ZMQ::LibZMQ3;
use ZMQ::Constants qw(:all);

my $connectors = {};
my $callbacks = {};
my $sockets = {};
my $polls = {};

sub new {
    my ($class, %options) = @_;
    my $connector  = {};
    $connector->{logger} = $options{logger};
    $connector->{identity} = $options{identity};
    $connector->{cipher} = $options{cipher};
    $connector->{vector} = $options{vector};
    $connector->{symkey} = undef;
    $connector->{pubkey} = centreon::centreond::common::loadpubkey(pubkey => $options{pubkey});
    $connector->{target_type} = $options{target_type};
    $connector->{target_path} = $options{target_path};
    $connector->{ping} = defined($options{ping}) ? $options{ping} : -1;
    $connector->{ping_timeout} = defined($options{ping_timeout}) ? $options{ping_timeout} : 30;
    $connector->{ping_progress} = 0; 
    $connector->{ping_time} = time();
    $connector->{ping_timeout_time} = time();
    
    $connectors->{$options{identity}} = $connector;
    bless $connector, $class;
    return $connector;
}

sub init {
    my ($self, %options) = @_;
    
    $self->{handshake} = 0;
    $sockets->{$self->{identity}} = centreon::centreond::common::connect_com(zmq_type => 'ZMQ_DEALER', name => $self->{identity},
                                                                             logger => $self->{logger},
                                                                             type => $self->{target_type},
                                                                             path => $self->{target_path});
    $callbacks->{$self->{identity}} = $options{callback} if (defined($options{callback}));
}

sub close {
    my ($self, %options) = @_;
    
    zmq_close($sockets->{$self->{identity}});
}

sub is_connected {
    my ($self, %options) = @_;
    
    # Should be connected (not 100% sure)
    if ($self->{handshake} == 2) {
        return (0, $self->{ping_time});
    }
    return -1;
}

sub ping {
    my ($self, %options) = @_;
    my $status = 0;
    
    if ($self->{ping} > 0 && $self->{ping_progress} == 0 && 
        time() - $self->{ping_time} > $self->{ping}) {
        $self->{ping_progress} = 1;
        $self->{ping_timeout_time} = time();
        my $action = defined($options{action}) ? $options{action} : 'PING';
        $self->send_message(action => $action, data => $options{data}, json_encode => $options{json_encode});
        $status = 1;
    }
    if ($self->{ping_progress} == 1 && 
        time() - $self->{ping_timeout_time} > $self->{ping_timeout}) {
        $self->{logger}->writeLogError("no ping response") if (defined($self->{logger}));
        $self->{ping_progress} = 0;
        zmq_close($sockets->{$self->{identity}});
        $self->init();
        push @{$options{poll}}, $self->get_poll();
        $status = 1;
    }
    
    push @{$options{poll}}, $self->get_poll();
    return $status;
}

sub get_poll {
    my ($self, %options) = @_;
    
    $polls->{$sockets->{$self->{identity}}} = {
            socket  => $sockets->{$self->{identity}},
            events  => ZMQ_POLLIN,
            callback => sub {
                event(identity => $self->{identity});
            }
    };
    return $polls->{$sockets->{$self->{identity}}};
}

sub event {
    my (%options) = @_;
    
    # We have a response. So it's ok :)
    if ($connectors->{$options{identity}}->{ping_progress} == 1) {
        $connectors->{$options{identity}}->{ping_progress} = 0;
    }
    $connectors->{$options{identity}}->{ping_time} = time();
    while (1) {
        my $message = centreon::centreond::common::zmq_dealer_read_message(socket => $sockets->{$options{identity}});
        
        # in progress
        if ($connectors->{$options{identity}}->{handshake} == 0 || $connectors->{$options{identity}}->{handshake} == 1) {
            my ($status, $symkey, $hostname) = centreon::centreond::common::client_get_secret(pubkey => $connectors->{$options{identity}}->{pubkey},
                                                                                              message => $message);
            if ($status == -1) {
                $connectors->{$options{identity}}->{handshake} = 0;
                return ;
            }
            $connectors->{$options{identity}}->{symkey} = $symkey;
            $connectors->{$options{identity}}->{handshake} = 2;
            if (defined($connectors->{$options{identity}}->{logger})) {
                $connectors->{$options{identity}}->{logger}->writeLogInfo("Client connected successfuly to '" . $connectors->{$options{identity}}->{target_type} . '//' . $connectors->{$options{identity}}->{target_path});
            }
        } else {
            my ($status, $data) = centreon::centreond::common::uncrypt_message(message => $message, 
                                                                               cipher => $connectors->{$options{identity}}->{cipher}, 
                                                                               vector => $connectors->{$options{identity}}->{vector}, symkey => $connectors->{$options{identity}}->{symkey});            
            
            if ($status == -1 || $data !~ /^\[(.+?)\]\s+\[(.*?)\]\s+(?:\[(.*?)\]\s*(.*)|(.*))$/m) {
                $connectors->{$options{identity}}->{handshake} = 0;
                return ;
            }
            
            if (defined($callbacks->{$options{identity}})) {
                $callbacks->{$options{identity}}->(identity => $options{identity}, data => $data);
            }
        }
        
        last unless (centreon::centreond::common::zmq_still_read(socket => $sockets->{$options{identity}}));
    }
}

sub send_message {
    my ($self, %options) = @_;
    
    if ($self->{handshake} == 0) {
        my $message = '[HELO] [' . $self->{identity} . ']';
        my ($status, $ciphertext) = centreon::centreond::common::client_helo_encrypt(pubkey => $self->{pubkey},
                                                                                     message => $message);
        if ($status == -1) {
            return (-1, 'crypt handshake issue'); 
        }
        $self->{handshake} = 1;

        zmq_sendmsg($sockets->{$self->{identity}}, $ciphertext);
        zmq_poll([$self->get_poll()], 10000);
    }
    
    if ($self->{handshake} == 1) {
        $self->{handshake} = 0;
        return (-1, 'Handshake timeout');
    }
    if ($self->{handshake} == 0) {
        return (-1, 'Handshake issue');
    }
    
    centreon::centreond::common::zmq_send_message(socket => $sockets->{$self->{identity}},
        cipher => $self->{cipher}, symkey => $self->{symkey}, vector => $self->{vector},
        %options);
    return 0;
}

1;
